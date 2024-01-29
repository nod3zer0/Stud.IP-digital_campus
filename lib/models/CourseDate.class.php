<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias column for termin_id
 * @property string $termin_id database column
 * @property string $range_id database column
 * @property string $autor_id database column
 * @property string $content database column
 * @property int $date database column
 * @property int $end_time database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $date_typ database column
 * @property string|null $raum database column
 * @property string|null $metadate_id database column
 * @property SimpleORMapCollection|Folder[] $folders has_many Folder
 * @property SimpleORMapCollection|RoomRequest[] $room_requests has_many RoomRequest
 * @property SimpleORMapCollection|ResourceRequestAppointment[] $resource_request_appointments has_many ResourceRequestAppointment
 * @property User $author belongs_to User
 * @property Course $course belongs_to Course
 * @property SeminarCycleDate|null $cycle belongs_to SeminarCycleDate
 * @property ResourceBooking $room_booking has_one ResourceBooking
 * @property SimpleORMapCollection|CourseTopic[] $topics has_and_belongs_to_many CourseTopic
 * @property SimpleORMapCollection|Statusgruppen[] $statusgruppen has_and_belongs_to_many Statusgruppen
 * @property SimpleORMapCollection|User[] $dozenten has_and_belongs_to_many User
 */

class CourseDate extends SimpleORMap implements PrivacyObject, Event
{
    const FORMAT_DEFAULT = 'default';
    const FORMAT_VERBOSE = 'verbose';

    private static $numbered_dates = null;

    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'termine';
        $config['has_and_belongs_to_many']['topics'] = [
            'class_name' => CourseTopic::class,
            'thru_table' => 'themen_termine',
            'order_by'   => 'ORDER BY priority',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['has_and_belongs_to_many']['statusgruppen'] = [
            'class_name' => Statusgruppen::class,
            'thru_table' => 'termin_related_groups',
            'order_by'   => 'ORDER BY position',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['has_and_belongs_to_many']['dozenten'] = [
            'class_name'  => User::class,
            'thru_table'  => 'termin_related_persons',
            'foreign_key' => 'termin_id',
            'thru_key'    => 'range_id',
            'order_by'    => 'ORDER BY Nachname, Vorname',
            'on_delete'   => 'delete',
            'on_store'    => 'store'
        ];
        $config['has_many']['folders'] = [
            'class_name' => Folder::class,
            'assoc_func' => 'findByTermin_id'
        ];
        $config['belongs_to']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'autor_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id'
        ];
        $config['belongs_to']['cycle'] = [
            'class_name'  => SeminarCycleDate::class,
            'foreign_key' => 'metadate_id'
        ];
        $config['has_one']['room_booking'] = [
            'class_name'        => ResourceBooking::class,
            'foreign_key'       => 'termin_id',
            'assoc_foreign_key' => 'range_id',
            'on_delete'         => 'delete',
            //'on_store'          => 'store'
        ];
        $config['has_many']['room_requests'] = [
            'class_name'        => RoomRequest::class,
            'assoc_foreign_key' => 'termin_id',
            'on_delete'         => 'delete',
        ];
        $config['has_many']['resource_request_appointments'] = [
            'class_name'        => ResourceRequestAppointment::class,
            'assoc_foreign_key' => 'appointment_id',
            'on_delete'         => 'delete',
        ];
        $config['default_values']['date_typ'] = 1;
        $config['registered_callbacks']['before_store'][] = 'cbStudipLog';
        $config['registered_callbacks']['after_create'][] = 'cbStudipLog';
        parent::configure($config);
    }

    /**
     * return consecutive number for a date in its course, if semester is given
     * only within that time range
     *
     * @param CourseDate $date
     * @param null|Semester $semester
     * @return int|null
     */
    public static function getConsecutiveNumber($date, $semester = null)
    {
        $semester_id = $semester ? $semester->id : 'all';

        if (!isset(self::$numbered_dates[$semester_id])) {
            $db = DBManager::get();
            $numbered = array_flip($db->fetchFirst("SELECT termin_id FROM termine WHERE range_id = ?" .
                ($semester ? " AND date BETWEEN ? AND ?" : "") .
                " ORDER BY date",
                $semester ? [$date->range_id, $semester->beginn, $semester->ende] : [$date->range_id]));
            self::$numbered_dates[$semester_id] = $numbered;
        }
        return isset(self::$numbered_dates[$semester_id][$date->termin_id])
             ? self::$numbered_dates[$semester_id][$date->termin_id] + 1
             : null;
    }

    /**
     * Returns course dates by issue id.
     *
     * @param String $issue_id Id of the issue
     * @return array with the associated dates
     */
    public static function findByIssue_id($issue_id)
    {
        return self::findBySQL("INNER JOIN themen_termine USING (termin_id)
            WHERE themen_termine.issue_id = ?
            ORDER BY date ASC",
            [$issue_id]
        );
    }

    /**
     * Returns course dates by course id
     *
     * @param String $seminar_id Id of the course
     * @return array with the associated dates
     */
    public static function findBySeminar_id($seminar_id)
    {
        return self::findByRange_id($seminar_id);
    }

    /**
     * Return course dates by range id (which is in many cases the course id)
     *
     * @param String $seminar_id Id of the course
     * @param String $order_by   Optional order definition
     * @return array with the associated dates
     */
    public static function findByRange_id($seminar_id, $order_by = 'ORDER BY date')
    {
        return parent::findByRange_id($seminar_id, $order_by);
    }

    /**
     * Returns course dates by issue id.
     *
     * @param String $issue_id Id of the issue
     * @return array with the associated dates
     */
    public static function findByStatusgruppe_id($group_id)
    {
        return self::findBySQL("INNER JOIN `termin_related_groups` USING (`termin_id`)
            WHERE `termin_related_groups`.`statusgruppe_id` = ?
            ORDER BY `date` ASC",
            [$group_id]
        );
    }

    /**
     * Adds a topic to this date.
     *
     * @param mixed $topic Topic definition (might be an id, an array or an
     *                     object)
     * @return int|false number addition of all return values, false if none was called
     */
    public function addTopic($topic)
    {
        $topic = CourseTopic::toObject($topic);
        if (!$this->topics->find($topic->id)) {
            $this->topics[] = $topic;
            return $this->storeRelations('topics');
        }
        return false;
    }

    /**
     * Removes a topic from this date.
     *
     * @param mixed $topic Topic definition (might be an id, an array or an
     *                     object)
     * @return number addition of all return values, false if none was called
     */
    public function removeTopic($topic)
    {
        $this->topics->unsetByPk(is_string($topic) ? $topic : $topic->id);
        return $this->storeRelations('topics');
    }

    /**
     * Returns the name of the assigned room for this date.
     *
     * @return String containing the room name
     */
    public function getRoomName()
    {
        if (Config::get()->RESOURCES_ENABLE && !empty($this->room_booking->resource)) {
            return $this->room_booking->resource->name;
        }
        return $this['raum'];
    }

    /**
     * Returns the assigned room for this date as an object.
     *
     * @return Room Either the object or null if no room is assigned
     */
    public function getRoom()
    {
        if (Config::get()->RESOURCES_ENABLE && !empty($this->room_booking->resource)) {
           return $this->room_booking->resource->getDerivedClassInstance();
        }
        return null;
    }

    /**
     * Returns the name of the type of this date.
     *
     * @param String containing the type name
     */
    public function getTypeName()
    {
        return $GLOBALS['TERMIN_TYP'][$this->date_typ]['name'] ?? '';
    }

    /**
     * Returns the full qualified name of this date.
     *
     * @param String $format Optional format type (only 'default', 'include-room' and
     *                       'verbose' are supported by now)
     * @return String containing the full name of this date.
     */
    public function getFullname($format = 'default')
    {
        if (!$this->date || !in_array($format, ['default', 'verbose', 'include-room'])) {
            return '';
        }

        $latter_template = $format === 'verbose' ? _('%R Uhr') : '%R';

        if (($this->end_time - $this->date) / 60 / 60 > 23) {
            $string = strftime('%a., %x (' . _('ganztägig') . ')' , $this->date);
        } else {
            $string =  strftime('%a., %x, %R', $this->date) . ' - '
                . strftime($latter_template, $this->end_time);
        }

        if($format === 'include-room') {
            $room = $this->getRoom();
            if($room) {
                $string = sprintf('%s <a href="%s" target="_blank">%s</a>',
                    $string,
                    $room->getActionURL('booking_plan'),
                    htmlReady($room->name)
                );
            }
        }
        return $string;
    }

    /**
     * Converts a CourseDate Entry to a CourseExDate Entry
     * returns instance of the new CourseExDate or NULL
     *
     * @return Object CourseExDate
     */
    public function cancelDate()
    {
        //NOTE: If you modify this method make sure the changes
        //are also inserted in SingleDateDB::storeSingleDate
        //and CourseExDate::unCancelDate to keep the behavior consistent
        //across Stud.IP!

        //These statements are used below to update the relations
        //of this date.
        $db = DBManager::get();

        $groups_stmt = $db->prepare(
            "UPDATE termin_related_groups
            SET termin_id = :ex_termin_id
            WHERE termin_id = :termin_id;"
        );

        $persons_stmt = $db->prepare(
            "UPDATE termin_related_persons
            SET range_id = :ex_termin_id
            WHERE range_id = :termin_id;"
        );

        $date = $this->toArray();

        $ex_date = new CourseExDate();
        $ex_date->setData($date);
        if ($room = $this->getRoom()) {
            $ex_date['resource_id'] = $room->getId();
        }
        $ex_date->setId($ex_date->getNewId());

        if ($ex_date->store()) {
            //Update some (but not all) relations to the date so that they
            //use the ID of the new ex-date.

            $groups_stmt->execute(
                [
                    'ex_termin_id' => $ex_date->id,
                    'termin_id' => $this->id
                ]
            );

            $persons_stmt->execute(
                [
                    'ex_termin_id' => $ex_date->id,
                    'termin_id' => $this->id
                ]
            );

            //After we updated the relations so that they refer to the
            //new ex-date we can delete this date and return the ex-date:
            $this->delete();
            return $ex_date;
        }
        return null;
    }

    /**
     * saves this object and expires the cache
     *
     * @see SimpleORMap::store()
     */
    public function store()
    {
        // load room-booking, if any
        $this->room_booking;

        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/'. $this->range_id);
        return parent::store();
    }

    /**
     * deletes this object and expires the cache
     *
     * @see SimpleORMap::delete()
     */
    public function delete()
    {
        $cache = StudipCacheFactory::getCache();
        $cache->expire('course/undecorated_data/'. $this->range_id);
        return parent::delete();
    }

    /**
     * @param $type string type of callback
     */
    protected function cbStudipLog($type)
    {
        if (!$this->metadate_id) {
            if ($type == 'after_create') {
                StudipLog::log('SEM_ADD_SINGLEDATE', $this->range_id, $this->getFullname());
            }
            if ($type == 'before_store' && !$this->isNew() && ($this->isFieldDirty('date') || $this->isFieldDirty('end_time'))) {
                $old_entry = self::build($this->content_db);
                StudipLog::log('SINGLEDATE_CHANGE_TIME', $this->range_id, $this->getFullname(), $old_entry->getFullname() . ' -> ' . $this->getFullname());
            }
        }
    }

    /**
     * Returns a list of all possible warnings that should be considered when
     * this date is deleted.
     *
     * @return array of warnings
     */
    public function getDeletionWarnings()
    {
        $warnings = [];
        if (count($this->topics) > 0) {
            $warnings[] = _('Diesem Termin ist ein Thema zugeordnet.');
        }

        if (Config::get()->RESOURCES_ENABLE && $this->getRoom()) {
            $warnings[] = _('Dieser Termin hat eine Raumbuchung, welche mit dem Termin gelöscht wird.');
        }

        return $warnings;
    }

    /**
     * return all filerefs belonging to this date, permissions fpr given user are checked
     *
     * @param string|User $user_or_id
     * @return mixed[] A mixed array with FolderType and FileRef objects.
     */
    public function getAccessibleFolderFiles($user_or_id)
    {
        $user_id = $user_or_id instanceof User ? $user_or_id->id : $user_or_id;
        $all_files = [];
        $all_folders = [];
        $folders = $this->folders->getArrayCopy();
        foreach ($this->topics as $topic) {
            $folders = array_merge($folders, $topic->folders->getArrayCopy());
        }
        foreach ($folders as $folder) {
            list($files, $typed_folders) = array_values(FileManager::getFolderFilesRecursive($folder->getTypedFolder(), $user_id));
            foreach ($files as $file) {
                $all_files[$file->id] = $file;
            }
            $all_folders = array_merge($all_folders, $typed_folders);
        }
        return ['files' => $all_files, 'folders' => $all_folders];
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("autor_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Termine'), 'termine', $field_data);
            }
        }
    }


    /**
     * @return string A string representation of the course date.
     */
    public function __toString() : string
    {
        return sprintf(
            _('Termin am %1$s, %2$s von %3$s bis %4$s Uhr'),
            strftime('%A', $this->date),
            strftime('%x', $this->date),
            date('H:i', $this->date),
            date('H:i', $this->end_time)
        );
    }

    //Start of Event interface implementation.

    public static function getEvents(DateTime $begin, DateTime $end, string $range_id): array
    {
        return self::findBySQL(
            "JOIN `seminar_user`
               ON `seminar_user`.`seminar_id` = `termine`.`range_id`
             WHERE `seminar_user`.`user_id` = :user_id
               AND `termine`.`date` BETWEEN :begin AND :end
               AND (
                   IFNULL(`termine`.`metadate_id`, '') = ''
                   OR `termine`.`metadate_id` NOT IN (
                       SELECT `metadate_id`
                       FROM `schedule_seminare`
                       WHERE `user_id` = :user_id
                         AND `visible` = 0
                 )
             )
             ORDER BY date",
            [
                'begin'   => $begin->getTimestamp(),
                'end'     => $end->getTimestamp(),
                'user_id' => $range_id
            ]
        );
    }

    //Event interface implementation:

    public function getObjectId() : string
    {
        return (string) $this->id;
    }

    public function getPrimaryObjectID(): string
    {
        return $this->range_id;
    }

    public function getObjectClass(): string
    {
        return static::class;
    }

    public function getTitle(): string
    {
        return $this->course->name ?? '';
    }

    public function getBegin(): DateTime
    {
        $begin = new DateTime();
        $begin->setTimestamp($this->date);
        return $begin;
    }

    public function getEnd(): DateTime
    {
        $end = new DateTime();
        $end->setTimestamp($this->end_time);
        return $end;
    }

    public function getDuration(): DateInterval
    {
        $begin = $this->getBegin();
        $end = $this->getEnd();
        return $end->diff($begin);
    }

    public function getLocation(): string
    {
        return $this->raum ?? '';
    }

    public function getUniqueId(): string
    {
        return sprintf('Stud.IP-SEM-%1$s@%2$s', $this->id, $_SERVER['SERVER_NAME']);
    }

    public function getDescription(): string
    {
        $descriptions = $this->topics->map(function ($topic) {
            $desc = $topic->title . "\n";
            $desc .= $topic->description;

            return $desc;
        });
        return implode("\n\n", $descriptions);
    }

    public function getAdditionalDescriptions(): array
    {
        $descriptions = [];
        if (count($this->dozenten) > 0) {
            $descriptions[_('Durchführende Lehrende')] = implode(', ', $this->dozenten->getFullname());
        }
        if (count($this->statusgruppen) > 0) {
            $descriptions[_('Beteiligte Gruppen')] = implode(', ', $this->statusgruppen->getValue('name'));
        }
        return $descriptions;
    }

    public function isAllDayEvent(): bool
    {
        //Course dates are never all day events.
        return false;
    }

    public function isWritable(string $user_id): bool
    {
        return $GLOBALS['perm']->have_studip_perm('dozent', $this->range_id, $user_id);
    }

    public function getCreationDate(): DateTime
    {
        $mkdate = new DateTime();
        $mkdate->setTimestamp($this->mkdate);
        return $mkdate;
    }

    public function getModificationDate(): DateTime
    {
        $chdate = new DateTime();
        $chdate->setTimestamp($this->chdate);
        return $chdate;
    }

    public function getImportDate(): DateTime
    {
        return $this->getCreationDate();
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function getEditor(): ?User
    {
        return null;
    }

    public function toEventData(string $user_id): \Studip\Calendar\EventData
    {
        $begin = new DateTime();
        $begin->setTimestamp($this->date);
        $end = new DateTime();
        $end->setTimestamp($this->end_time);

        $membership = CourseMember::findOneBySQL(
            'seminar_id = :course_id AND user_id = :user_id',
            ['course_id' => $this->range_id, 'user_id' => $user_id]
        );
        $class_names = [];
        if ($membership) {
            $class_names[] = sprintf('gruppe%u', $membership->status);
        }
        $studip_view_urls = [];
        if ($GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user_id)) {
            $studip_view_urls['show'] = URLHelper::getURL('dispatch.php/course/dates/details/' . $this->id, ['cid' => $this->range_id, 'extra_buttons' => '1']);
        }

        return new \Studip\Calendar\EventData(
            $begin,
            $end,
            $this->getTitle(),
            $class_names,
            '#000000',
            '#aaaaaa',
            $this->isWritable($user_id),
            CourseDate::class,
            $this->id,
            Course::class,
            $this->range_id,
            'course',
            $this->range_id,
            $studip_view_urls,
            [],
            'seminar',
            'rgba(0,0,0,0)'
        );
    }

    //End of Event interface implementation.
}
