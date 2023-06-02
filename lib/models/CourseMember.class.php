<?php
/**
 * CourseMember.class.php
 * model class for table seminar_user
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
 * @property string seminar_id database column
 * @property string user_id database column
 * @property string status database column
 * @property string position database column
 * @property string gruppe database column
 * @property string notification database column
 * @property string mkdate database column
 * @property string comment database column
 * @property string visible database column
 * @property string label database column
 * @property string bind_calendar database column
 * @property string vorname computed column read/write
 * @property string nachname computed column read/write
 * @property string username computed column read/write
 * @property string email computed column read/write
 * @property string title_front computed column read/write
 * @property string title_rear computed column read/write
 * @property string course_name computed column read/write
 * @property string id computed column read/write
 * @property SimpleORMapCollection datafields has_many DatafieldEntryModel
 * @property User user belongs_to User
 * @property Course course belongs_to Course
 */
class CourseMember extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminar_user';
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'seminar_id',
        ];
        $config['has_many']['datafields'] = [
            'class_name' => DatafieldEntryModel::class,
            'assoc_foreign_key' =>
                function($model, $params) {
                    list($sec_range_id, $range_id) = (array)$params[0]->getId();
                    $model->setValue('range_id', $range_id);
                    $model->setValue('sec_range_id', $sec_range_id);
                },
            'assoc_func' => 'findByModel',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'foreign_key' =>
                function($course_member) {
                    return [$course_member];
                }
        ];

        $config['additional_fields']['vorname']     = ['user', 'vorname'];
        $config['additional_fields']['nachname']    = ['user', 'nachname'];
        $config['additional_fields']['username']    = ['user', 'username'];
        $config['additional_fields']['email']       = ['user', 'email'];
        $config['additional_fields']['title_front'] = ['user', 'title_front'];
        $config['additional_fields']['title_rear']  = ['user', 'title_rear'];

        $config['additional_fields']['course_name'] = [];

        $config['registered_callbacks']['after_delete'][] = 'cbRemoveNotifications';

        parent::configure($config);
    }

    public static function findByCourse($course_id)
    {
        $query = "SELECT seminar_user.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                         FROM seminar_user
                         LEFT JOIN auth_user_md5 aum USING (user_id)
                         LEFT JOIN user_info ui USING (user_id)
                         WHERE seminar_id = ?
                         ORDER BY position, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$course_id],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByCourseAndStatus($course_id, $status)
    {
        $query = "SELECT seminar_user.*, aum.Vorname, aum.Nachname, aum.Email,
                         aum.username, ui.title_front, ui.title_rear
                  FROM seminar_user
                  LEFT JOIN auth_user_md5 aum USING (user_id)
                  LEFT JOIN user_info ui USING (user_id)
                  WHERE seminar_id = ?
                    AND seminar_user.status IN (?)
                  ORDER BY status, position, Nachname, Vorname";
        return DBManager::get()->fetchAll(
            $query,
            [$course_id, is_array($status) ? $status : words($status)],
            __CLASS__ . '::buildExisting'
        );
    }

    public static function findByUser($user_id)
    {
        $query = "SELECT seminar_user.*, seminare.Name AS course_name
                  FROM seminar_user
                  LEFT JOIN seminare USING (seminar_id)
                  WHERE user_id = ?
                  ORDER BY seminare.Name";
        return DBManager::get()->fetchAll(
            $query,
            [$user_id],
            __CLASS__ . '::buildExisting'
        );
    }

    /**
     * Retrieves the number of all members of a status
     *
     * @param String|Array $status  the status to filter with
     *
     * @return int the number of all those members.
     */
    public static function countByCourseAndStatus($course_id, $status)
    {
        return self::countBySql(
            'seminar_id = ? AND status IN(?)',
            [$course_id, is_array($status) ? $status : words($status)]
        );
    }

    public function getUserFullname($format = 'full')
    {
        return User::build(array_merge(
            ['motto' => ''],
            $this->toArray('vorname nachname username title_front title_rear')
        ))->getFullname($format);
    }

    public function cbRemoveNotifications()
    {
        CourseMemberNotification::deleteBySQL(
            'user_id = ? AND seminar_id = ?',
            [$this->user_id, $this->seminar_id]
        );
    }

    /**
     * Get members of a course
     *
     * @param string $course_id
     * @param string $sort_status
     * @param string $order_by
     * @return array
     */
    public static function getMembers(string $course_id, string $sort_status = 'autor', string $order_by = 'nachname asc'): array
    {
        list($order, $asc) = explode(' ', $order_by);
        if ($order === 'nachname') {
            $order_by = "Nachname {$asc},Vorname {$asc}";
        }

        $query = "SELECT su.user_id, username, Vorname, Nachname, Email, status,
                         position, su.mkdate, su.visible, su.comment,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                  FROM seminar_user AS su
                  INNER JOIN auth_user_md5 USING (user_id)
                  INNER JOIN user_info USING (user_id)
                  WHERE seminar_id = ?
                  ORDER BY position, Nachname ASC";
        $st = DBManager::get()->prepare($query);
        $st->execute([$course_id]);
        $members = SimpleCollection::createFromArray($st->fetchAll(PDO::FETCH_ASSOC));
        $filtered_members = [];

        foreach (['user', 'autor', 'tutor', 'dozent'] as $status) {
            $filtered_members[$status] = $members->findBy('status', $status);
            if ($status === $sort_status) {
                $filtered_members[$status]->orderBy($order_by, $order !== 'nachname' ? SORT_NUMERIC : SORT_LOCALE_STRING);
            } else {
                $filtered_members[$status]->orderBy(in_array($status, ['tutor', 'dozent']) ? 'position,Nachname,Vorname' : 'Nachname,Vorname');
            }
        }
        return $filtered_members;
    }

    /**
     * Get user informations by first and last name for csv-import
     * @param string $course_id
     * @param string $nachname
     * @param string $vorname
     * @return array
     */
    public static function getMemberByIdentification(string $course_id, string $nachname, string $vorname = null): array
    {
        return DBManager::get()->fetchAll("SELECT
                    auth_user_md5.user_id,
                    auth_user_md5.username,
                    auth_user_md5.perms,
                    seminar_user.Seminar_id AS is_present,
                    {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                 FROM auth_user_md5
                 LEFT JOIN user_info USING (user_id)
                 LEFT JOIN seminar_user ON (seminar_user.user_id = auth_user_md5.user_id AND seminar_user.Seminar_id = ?)
                 WHERE auth_user_md5.perms IN ('autor', 'tutor', 'dozent')
                 AND auth_user_md5.visible <> 'never'
                 AND auth_user_md5.Nachname LIKE ? AND (? IS NULL OR auth_user_md5.Vorname LIKE ?)
                 ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname",
            [$course_id, $nachname, $vorname, $vorname]);
    }

    /**
     * Get user informations by username for csv-import
     * @param string $course_id
     * @param string $username
     * @return Array
     */
    public static function getMemberByUsername(string $course_id, string $username): array
    {
        return DBManager::get()->fetchAll(
            "SELECT auth_user_md5.user_id,
                    auth_user_md5.username,
                    auth_user_md5.perms,
                    seminar_user.Seminar_id AS is_present,
                    {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
             FROM auth_user_md5
             LEFT JOIN user_info USING (user_id)
             LEFT JOIN seminar_user ON (seminar_user.user_id = auth_user_md5.user_id AND seminar_user.Seminar_id = ?)
             WHERE auth_user_md5.perms IN ('autor', 'tutor', 'dozent')
             AND auth_user_md5.visible <> 'never'
               AND auth_user_md5.username LIKE ?
             ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname",
            [$course_id, $username]
        );
    }

    /**
     * Get user informations by email for csv-import
     * @param String $course_id
     * @param String $email
     * @return Array
     */
    public static function getMemberByEmail($course_id, $email): array
    {
        return DBManager::get()->fetchAll(
            "SELECT a.user_id, username,
                    perms, b.Seminar_id AS is_present
             FROM auth_user_md5 AS a
             LEFT JOIN user_info USING (user_id)
             LEFT JOIN seminar_user AS b ON (b.user_id = a.user_id AND b.Seminar_id = ?)
             WHERE a.perms IN ('autor', 'tutor', 'dozent')
             AND a.visible <> 'never'
               AND email LIKE ?
             ORDER BY Nachname, Vorname",
            [$course_id, $email]
        );
    }

    /**
     * Get user informations by generic datafields for csv-import
     * @param string $course_id
     * @param string $nachname
     * @param string $datafield_id
     * @return Array
     */
    public static function getMemberByDatafield(string $course_id, string $nachname,  string $datafield_id): array
    {
        // TODO Fullname
        return DBManager::get()->fetchAll(
            "SELECT
                auth_user_md5.user_id,
                auth_user_md5.username,
                seminar_user.Seminar_id AS is_present,
                {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
             FROM datafields_entries
             LEFT JOIN auth_user_md5 ON (auth_user_md5.user_id = datafields_entries.range_id)
             LEFT JOIN user_info USING (user_id)
             LEFT JOIN seminar_user ON (seminar_user.user_id = auth_user_md5.user_id AND seminar_user.Seminar_id = ?)
             WHERE auth_user_md5.perms IN ('autor', 'tutor', 'dozent')
             AND auth_user_md5.visible <> 'never'
               AND datafields_entries.datafield_id = ? AND datafields_entries.content = ?
             ORDER BY auth_user_md5.Nachname, auth_user_md5.Vorname",
            [$course_id, $datafield_id, $nachname]
        );
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
                $storage->addTabularData(_('SeminareUser'), 'seminar_user', $field_data);
            }
        }
    }

    /**
     * return the highest position-number increased by one for the
     * passed user-group in the passed seminar
     *
     * @param string $status     can be on of 'tutor', 'dozent', ...
     * @param string $seminar_id the seminar to work on
     *
     * @return int  the next available position
     */
    public static function getNextPosition(string $status, string $seminar_id): int
    {
        return (int) DBManager::get()->fetchColumn(
            "SELECT MAX(position) + 1
              FROM seminar_user
              WHERE Seminar_id = ? AND status = ?",
            [$seminar_id, $status]
        );
    }

    /**
     * reset the order-positions for the given status in the passed seminar,
     * starting at the passed position
     *
     * @param string $course_id     the seminar to work on
     * @param int    $position the position to start with
     *
     * @return void
     */
    public static function resortMembership(string $course_id, int $position, string $status = 'tutor')
    {
        self::findEachBySQL(
            function (CourseMember $membership) {
                $membership->position = $membership->position - 1;
                $membership->store();
            },
            "Seminar_id = ? AND position > ? AND status = ? ",
            [$course_id, $position, $status]
        );
    }
    /**
     * Insert a user into a seminar with optional log-message and contingent
     *
     * @param string   $seminar_id
     * @param string   $user_id
     * @param string   $status       status of user in the seminar (user, autor, tutor, dozent)
     * @param boolean  $copy_studycourse  if true, the studycourse is copied from admission_seminar_user
     *                                    to seminar_user. Overrides the $contingent-parameter
     * @param string   $contingent   optional studiengang_id, if no id is given, no contingent is considered
     * @param string   $log_message  optional log-message. if no log-message is given a default one is used
     * @return bool
     */
    public static function insertCourseMember($seminar_id, $user_id, $status, $copy_studycourse = false, $contingent = false, $log_message = false): bool
    {
        if (!$user_id) {
            return false;
        }
        // get the seminar-object
        $sem = Seminar::GetInstance($seminar_id);

        $admission_status = '';
        $admission_comment = '';
        $mkdate = time();

        $admission_user = AdmissionApplication::find([$user_id, $seminar_id]);
        if ($admission_user) {
            $admission_status = $admission_user->status;
            $admission_comment = $admission_user->comment ?? '';
            $mkdate = $admission_user->mkdate;
        }

        // check if there are places left in the submitted contingent (if any)
        //ignore if preliminary
        if ($admission_status !== 'accepted' && $contingent && $sem->isAdmissionEnabled() && !$sem->getFreeAdmissionSeats()) {
            return false;
        }

        // get coloured group as used on meine_seminare
        $colour_group = $sem->getDefaultGroup();

        // LOGGING
        // if no log message is submitted use a default one
        if (!$log_message) {
            $log_message = 'Wurde in die Veranstaltung eingetragen, admission_status: '. $admission_status . ' Kontingent: ' . $contingent;
        }
        StudipLog::log('SEM_USER_ADD', $seminar_id, $user_id, $status, $log_message);
        $membership = new self([$seminar_id, $user_id]);
        $membership->setData([
            'Seminar_id' => $seminar_id,
            'user_id'    => $user_id,
            'status'     => $status,
            'comment'    => $admission_comment,
            'gruppe'     => $colour_group,
            'mkdate'     => $mkdate,
        ]);
        $membership->store();

        NotificationCenter::postNotification('UserDidEnterCourse', $seminar_id, $user_id);

        if ($admission_status) {
            $admission_user->delete();

            //renumber the waiting/accepted/lot list, a user was deleted from it
            AdmissionApplication::renumberAdmission($seminar_id);
        }
        $cs = $sem->getCourseSet();
        if ($cs) {
            AdmissionPriority::unsetPriority($cs->getId(), $user_id, $sem->getId());
        }

        CalendarScheduleModel::deleteSeminarEntries($user_id, $seminar_id);

        // reload the seminar, the contingents have changed
        $sem->restore();

        // Check if a parent course exists and insert user there.
        if ($sem->parent_course) {
            self::insertCourseMember($sem->parent_course, $user_id, $status, $copy_studycourse, $contingent, $log_message);
        }

        return true;
    }
}
