<?php
/**
 * InstituteMember
 * model class for table user_inst
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2012 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string user_id database column
 * @property string institut_id database column
 * @property string inst_perms database column
 * @property string sprechzeiten database column
 * @property string raum database column
 * @property string telefon database column
 * @property string fax database column
 * @property string externdefault database column
 * @property string priority database column
 * @property string visible database column
 * @property string vorname computed column read/write
 * @property string nachname computed column read/write
 * @property string username computed column read/write
 * @property string email computed column read/write
 * @property string title_front computed column read/write
 * @property string title_rear computed column read/write
 * @property string institute_name computed column read/write
 * @property string id computed column read/write
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property User user belongs_to User
 * @property Institute institute belongs_to Institute
 */
class InstituteMember extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'user_inst';
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['institute'] = [
            'class_name' => Institute::class,
            'foreign_key' => 'institut_id',
        ];
        $config['has_many']['datafields'] = [
            'class_name' => DatafieldEntryModel::class,
            'assoc_foreign_key' =>
                function($model, $params) {
                    $model->setValue('range_id', $params[0]->user_id);
                    $model->setValue('sec_range_id', $params[0]->institut_id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($institute_member) {
                    return [$institute_member];
                }
        ];

        $config['additional_fields']['vorname']     = ['user', 'vorname'];
        $config['additional_fields']['nachname']    = ['user', 'nachname'];
        $config['additional_fields']['username']    = ['user', 'username'];
        $config['additional_fields']['email']       = ['user', 'email'];
        $config['additional_fields']['title_front'] = ['user', 'title_front'];
        $config['additional_fields']['title_rear']  = ['user', 'title_rear'];
        $config['additional_fields']['user_info']   = ['user', 'info'];

        $config['additional_fields']['institute_name'] = [];

        $config['registered_callbacks']['after_delete'][] = function ($member) {
            $institute = $member->institute;
            $user_id   = $member->user_id;

            if ($institute) {
                $institute->status_groups->removeUser($user_id, true);
            }
        };

        parent::configure($config);
    }

    public static function findByInstitute($institute_id)
    {
        $query = "SELECT user_inst.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                  FROM user_inst
                  LEFT JOIN auth_user_md5 aum USING (user_id)
                  LEFT JOIN user_info ui USING (user_id)
                  WHERE institut_id = ?
                    AND inst_perms <> 'user'
                  ORDER BY inst_perms, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$institute_id],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByInstituteAndStatus($institute_id, $status)
    {
        $query = "SELECT user_inst.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                  FROM user_inst
                  LEFT JOIN auth_user_md5 aum USING (user_id)
                  LEFT JOIN user_info ui USING (user_id)
                  WHERE institut_id = ?
                    AND user_inst.inst_perms IN (?)
                  ORDER BY inst_perms, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$institute_id, is_array($status) ? $status : words($status)],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByUser($user_id)
    {
        $query = "SELECT user_inst.*, Institute.Name AS institute_name
                  FROM user_inst
                  JOIN Institute USING (institut_id)
                  WHERE user_id = ?
                  ORDER BY priority, Institute.Name";
        return DBManager::get()->fetchAll(
            $query,
            [$user_id],
            __CLASS__ . '::buildExisting'
        );
    }

    /**
     * Finds an institute membership by user and institute id.
     *
     * @param string $user_id
     * @param string $institute_id
     * @return InstituteMember|null
     */
    public static function findByUserAndInstitute($user_id, $institute_id)
    {
        return self::findOneBySQL('user_id = ? AND institut_id = ?', [$user_id, $institute_id]);
    }

    public function getUserFullname($format = 'full')
    {
        return User::build(array_merge(
            ['motto' => ''],
            $this->toArray('vorname nachname username title_front title_rear')
        ))->getFullname($format);
    }

    /**
     * Returns the id of the default institute for a user or false if none is set.
     *
     * @param string $user_id Id of the user
     * @return string institute id or bool false
     */
    public static function getDefaultInstituteIdForUser($user_id)
    {
        $institute = self::findOneBySQL(
            "user_id = ? AND inst_perms != 'user' AND externdefault = 1",
            [$user_id]
        );
        return $institute ? $institute->institut_id : false;
    }

    /**
     * Returns the id of the default institute for a user or false if none is set.
     *
     * @param string $user_id Id of the user
     * @return bool true if institute was updated, false otherwise
     */
    public static function ensureDefaultInstituteForUser($user_id)
    {
        $institute = self::findOneBySQL(
            "user_id = ? AND inst_perms != 'user' ORDER BY externdefault = 1 DESC, priority",
            [$user_id]
        );
        if (!$institute || $institute->externdefault) {
            return false;
        }

        $institute->externdefault = true;
        $institute->store();

        return true;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL('user_id = ?', [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Einrichtungs Informationen'), 'user_inst', $field_data);
            }
        }
    }
}
