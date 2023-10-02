<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2015 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias for pk
 * @property string $termin_id database column
 * @property string $event_id alias column for termin_id
 * @property string $range_id database column
 * @property string $sem_id alias column for range_id
 * @property string $autor_id database column
 * @property string $author_id alias column for autor_id
 * @property string $content database column
 * @property int $date database column
 * @property int $start alias column for date
 * @property int $end_time database column
 * @property int $end alias column for end_time
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $date_typ database column
 * @property int $category_intern alias column for date_typ
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
 * @property-read mixed $location additional field
 * @property mixed $type additional field
 * @property-read mixed $name additional field
 * @property-read mixed $title additional field
 * @property-read mixed $editor_id additional field
 * @property-read mixed $uid additional field
 * @property-read mixed $summary additional field
 * @property-read mixed $description additional field
 */

class CourseMarkedEvent extends CourseEvent
{

    protected static function configure($config= [])
    {
        parent::configure($config);
    }

    /**
     * Returns all CourseMarkedEvents in the given time range for the given range_id.
     *
     * @param string $user_id Id of Stud.IP object from type user, course, inst
     * @param DateTime $start The start date time.
     * @param DateTime $end The end date time.
     * @return SimpleORMapCollection Collection of found CourseMarkedEvents.
     */
    public static function getEventsByInterval($user_id, DateTime $start, dateTime $end)
    {
        $stmt = DBManager::get()->prepare('SELECT DISTINCT termine.* FROM schedule_seminare '
                . 'INNER JOIN termine ON schedule_seminare.seminar_id = range_id '
                . 'LEFT JOIN seminar_user ON seminar_user.seminar_id = range_id AND seminar_user.user_id= :user_id '
                . 'WHERE schedule_seminare.user_id = :user_id AND schedule_seminare.visible = 1 '
                . 'AND seminar_user.seminar_id IS NULL AND date BETWEEN :start AND :end '
                . 'ORDER BY date ASC');
        $stmt->execute([
            ':user_id' => $user_id,
            ':start'   => $start->getTimestamp(),
            ':end'     => $end->getTimestamp()
        ]);
        $event_collection = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $event = new CourseMarkedEvent();
            $event->setData($row);
            $event->setNew(false);
            $event_collection[] = $event;
        }
        $event_collection = SimpleORMapCollection::createFromArray($event_collection, false);
        $event_collection->setClassName('Event');
        return $event_collection;
    }

    public function getPermission($user_id = null)
    {
        return Event::PERMISSION_READABLE;
    }

    /**
     * Returns the title of this event.
     * The title of a course event is the name of the course or if a topic is
     * assigned, the title of this topic. If the user has not the permission
     * Event::PERMISSION_READABLE, the title is "Keine Berechtigung.".
     *
     * @return string
     */
    public function getTitle()
    {
        $title = $this->course->name;
        $title .= ' ' . _('(vorgemerkt)');

        return $title;
    }

    /**
     * Returns the index of the category.
     * If the user has no permission, 255 is returned.
     *
     * TODO remove? use getStudipCategory instead?
     *
     * @see config/config.inc.php $TERMIN_TYP
     * @return int The index of the category
     */
    public function getCategory()
    {
        return 256;
    }

    public function getDescription()
    {
        return '';
    }

}
