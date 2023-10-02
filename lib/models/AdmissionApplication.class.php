<?php
/**
 * AdmissionApplication.class.php
 * model class for table admission_seminar_user
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
 * @property array $id alias for pk
 * @property string $user_id database column
 * @property string $seminar_id database column
 * @property string $status database column
 * @property int $mkdate database column
 * @property int|null $chdate database column
 * @property int|null $position database column
 * @property string $comment database column
 * @property string $visible database column
 * @property User $user belongs_to User
 * @property Course $course belongs_to Course
 * @property mixed $vorname additional field
 * @property mixed $nachname additional field
 * @property mixed $username additional field
 * @property mixed $email additional field
 * @property mixed $title_front additional field
 * @property mixed $title_rear additional field
 * @property mixed $course_name additional field
 */
class AdmissionApplication extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'admission_seminar_user';
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name' => Course::class,
            'foreign_key' => 'seminar_id',
        ];
        $config['additional_fields']['vorname'] = ['user', 'vorname'];
        $config['additional_fields']['nachname'] = ['user', 'nachname'];
        $config['additional_fields']['username'] = ['user', 'username'];
        $config['additional_fields']['email'] = ['user', 'email'];
        $config['additional_fields']['title_front'] = ['user', 'title_front'];
        $config['additional_fields']['title_rear'] = ['user', 'title_rear'];
        $config['additional_fields']['course_name'] = [];
        parent::configure($config);
    }

    public static function findByCourse($course_id)
    {
        $db = DBManager::get();
        return $db->fetchAll("SELECT admission_seminar_user.*, aum.vorname,aum.nachname,aum.email,
                             aum.username,ui.title_front,ui.title_rear
                             FROM admission_seminar_user
                             LEFT JOIN auth_user_md5 aum USING (user_id)
                             LEFT JOIN user_info ui USING (user_id)
                             WHERE seminar_id = ? ORDER BY position",
                             [$course_id],
                             __CLASS__ . '::buildExisting');
    }

    public static function findByUser($user_id)
    {
        $db = DBManager::get();
        return $db->fetchAll("SELECT admission_seminar_user.*, seminare.Name as course_name
                             FROM admission_seminar_user
                             LEFT JOIN seminare USING (seminar_id)
                             WHERE user_id = ? ORDER BY seminare.Name",
                             [$user_id],
                             __CLASS__ . '::buildExisting');
    }

    public function getUserFullname($format = 'full')
    {
        return User::build(array_merge(['motto' => ''], $this->toArray('vorname nachname username title_front title_rear')))->getFullname($format);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findByUser($storage->user_id);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Wartelisten'), 'admission_seminar_user', $field_data);
            }
        }
    }

    /**
     * @param string $course_id
     * @param string $sort_status
     * @param string $order_by
     * @return array
     */
    public static function getAdmissionMembers(string $course_id, string $sort_status = 'autor', string $order_by = 'nachname asc'): array
    {
        [$order, $asc] = explode(' ', $order_by);
        if ($order === 'nachname') {
            $order_by = "nachname {$asc},vorname {$asc}";
        }

        $cs = CourseSet::getSetForCourse($course_id);
        $claiming = [];
        if (is_object($cs) && !$cs->hasAlgorithmRun()) {
            foreach (AdmissionPriority::getPrioritiesByCourse($cs->getId(), $course_id) as $user_id => $p) {
                $user = User::find($user_id);
                $data = $user->toArray('user_id username vorname nachname email');
                $data['fullname'] = $user->getFullname('full_rev');
                $data['position'] = $cs->hasAdmissionRule('LimitedAdmission') ? $p : '-';
                $data['visible'] = 'unknown';
                $data['status'] = 'claiming';
                $claiming[] = $data;
            }
        }

        $query = "SELECT asu.user_id, username, Vorname, Nachname, Email, status,
                         position, asu.mkdate, asu.visible, asu.comment,
                         {$GLOBALS['_fullname_sql']['full_rev']} AS fullname
                  FROM admission_seminar_user AS asu
                  JOIN auth_user_md5 USING (user_id)
                  JOIN user_info USING (user_id)
                  WHERE seminar_id = ?
                  ORDER BY position, Nachname";
        $st = DBManager::get()->prepare($query);
        $st->execute([$course_id]);
        $application_members = SimpleCollection::createFromArray(array_merge($claiming, $st->fetchAll(PDO::FETCH_ASSOC)));
        $filtered_members = [];
        foreach (['awaiting', 'accepted', 'claiming'] as $status) {
            $filtered_members[$status] = $application_members->findBy('status', $status);
            if ($status === $sort_status) {
                $filtered_members[$status]->orderBy($order_by, $order !== 'nachname' ? SORT_NUMERIC : SORT_LOCALE_STRING);
            }
        }
        return $filtered_members;
    }

    /**
     * returns the position for a user on a waiting list
     *
     * if the user is not found false is returned, return true if the user is found but
     * no position is available
     *
     * @param        string $user_id user_id
     * @param        string $seminar_id seminar_id
     * @return       bool position in waiting list or false if not found
     *
     */
    public static function checkMemberPosition(string $user_id, string $seminar_id): bool
    {
        $position = DBManager::get()->fetchColumn("SELECT IFNULL(position, 'na')
              FROM admission_seminar_user
              WHERE user_id = ? AND seminar_id = ? AND status = 'awaiting'",
            [$user_id, $seminar_id]
        );

        return $position === 'na';
    }

    /**
     * @param string $seminar_id
     * @param string $send_message
     * @return void
     * @throws NotificationVetoException
     */
    public static function addMembers(string $seminar_id, bool $send_message = true): void
    {
        $messaging = new messaging;

        //Daten holen / Abfrage ob ueberhaupt begrenzt
        $seminar = Seminar::GetInstance($seminar_id, true);

        if($seminar->isAdmissionEnabled()){
            $sem_preliminary = ($seminar->admission_prelim == 1);
            $cs = $seminar->getCourseSet();
            //Veranstaltung einfach auffuellen (nach Lostermin und Ende der Kontingentierung)
            if (!$seminar->admission_disable_waitlist_move && $cs->hasAlgorithmRun()) {
                $count = (int)$seminar->getFreeAdmissionSeats();
                $memberships = self::findBySQL(
                    "seminar_id = ? AND status = 'awaiting' ORDER BY position LIMIT {$count}",
                    [$seminar_id]
                );
                foreach ($memberships as $membership) {
                    //ok, here ist the "colored-group" meant (for grouping on meine_seminare), not the grouped seminars as above!
                    $group = select_group($seminar->getSemesterStartTime());
                    if (!$sem_preliminary) {
                        $course_membership = new CourseMember([$seminar_id, $membership->id]);
                        $course_membership->setData([
                            'status'     => 'autor',
                            'gruppe'     => $group,
                        ]);
                        $affected = $course_membership->store();

                        NotificationCenter::postNotification('UserDidEnterCourse', $seminar->getId(), $membership->user_id);
                    } else {
                        $membership->status = 'accepted';
                        $affected = $membership->store();
                    }
                    if ($affected > 0) {
                        $log_message = 'Wurde automatisch aus der Warteliste in die Veranstaltung eingetragen.';
                        StudipLog::log('SEM_USER_ADD', $seminar->getId(), $membership->user_id, $sem_preliminary ? 'accepted' : 'autor', $log_message);
                        if (!$sem_preliminary) {
                            $affected = $membership->delete();
                        } else {
                            $affected = 0;
                        }
                        //User benachrichtigen
                        if (($sem_preliminary || $affected > 0) && $send_message) {
                            setTempLanguage($membership->user_id);
                            if (!$sem_preliminary) {
                                $message = sprintf (_('Sie sind in die Veranstaltung **%s (%s)** eingetragen worden, da für Sie ein Platz frei geworden ist. Damit sind Sie für die Teilnahme an der Veranstaltung zugelassen. Ab sofort finden Sie die Veranstaltung in der Übersicht Ihrer Veranstaltungen.'), $seminar->getName(), $seminar->getFormattedTurnus(true));
                            } else {
                                $message = sprintf (_('Sie haben den Status vorläufig akzeptiert in der Veranstaltung **%s (%s)** erhalten, da für Sie ein Platz frei geworden ist.'), $seminar->getName(), $seminar->getFormattedTurnus(true));
                            }
                            $subject = sprintf(_("Teilnahme an der Veranstaltung %s"), $seminar->getName());
                            restoreLanguage();

                            $messaging->insert_message($message, $membership->username, '____%system%____', false, false, '1', false, $subject, true);
                        }
                    }
                }
                //Warteposition der restlichen User neu eintragen
                AdmissionApplication::renumberAdmission($seminar_id, FALSE);
            }
            $seminar->restore();
        }
    }

    /**
     * Renumber admissions
     * @param string $seminar_id
     * @param bool $send_message
     * @return void
     */
    public static function renumberAdmission (string $seminar_id, bool $send_message = true): void
    {
        $messaging = new messaging;
        $seminar = Seminar::GetInstance($seminar_id);
        if ($seminar->isAdmissionEnabled()) {
            $admission_users = self::findBySQL(
                "seminar_id = ? AND status = 'awaiting' ORDER BY position",
                [$seminar->id]
            );
            $position = 1;
            foreach ($admission_users as $admission) {
                $admission->position = $position;
                if ($admission->store() && Config::get()->NOTIFY_ON_WAITLIST_ADVANCE && $send_message) {
                    $username = $admission->user->username;
                    setTempLanguage($admission->user_id);
                    $message = sprintf(_('Sie sind auf der Warteliste der Veranstaltung **%s (%s)** hochgestuft worden. Sie stehen zur Zeit auf Position %s.'),
                        $seminar->name,
                        $seminar->getFormattedTurnus(),
                        $position);
                    $subject = sprintf(_('Ihre Position auf der Warteliste der Veranstaltung %s wurde verändert'), $seminar->name);
                    restoreLanguage();

                    $messaging->insert_message($message, $username, '____%system%____', FALSE, FALSE, '1', FALSE, $subject);
                }
                $position += 1;
            }
        }
    }
}
