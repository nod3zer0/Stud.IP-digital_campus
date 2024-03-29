<?php
/**
 * UserStudyCourse.class.php
 * model class for table user_studiengang
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property array $id alias for pk
 * @property string $user_id database column
 * @property string $fach_id database column
 * @property int|null $semester database column
 * @property string $abschluss_id database column
 * @property string|null $version_id database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property User $user belongs_to User
 * @property Abschluss $degree belongs_to Abschluss
 * @property Fach $studycourse belongs_to Fach
 * @property mixed $degree_name additional field
 * @property mixed $studycourse_name additional field
 */
class UserStudyCourse extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'user_studiengang';

        $config['belongs_to']['user'] = [
            'class_name' => 'User',
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['degree'] = [
            'class_name' => 'Abschluss',
            'foreign_key' => 'abschluss_id',
        ];
        $config['belongs_to']['studycourse'] = [
            'class_name' => 'Fach',
            'foreign_key' => 'fach_id',
        ];

        $config['additional_fields']['degree_name'] = [];
        $config['additional_fields']['studycourse_name'] = [];
        parent::configure($config);
    }

    public static function findByUser($user_id)
    {
        $db = DBManager::get();
        $st = $db->prepare("SELECT user_studiengang.*, abschluss.name as degree_name,
                            fach.name as studycourse_name
                            FROM user_studiengang
                            LEFT JOIN abschluss USING (abschluss_id)
                            LEFT JOIN fach USING (fach_id)
                            WHERE user_id = ? ORDER BY studycourse_name,degree_name");
        $st->execute([$user_id]);
        $ret = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = self::buildExisting($row);
        }
        return $ret;
    }

    public static function findByStudyCourseAndDegree($study_course_id, $degree_id)
    {
        return self::findBySql("fach_id = ? AND abschluss_id = ?", [$study_course_id, $degree_id]);
    }
    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('UserStudiengang'), 'user_studiengang', $field_data);
            }
        }
    }
}
