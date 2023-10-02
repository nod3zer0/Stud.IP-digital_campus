<?php

/**
 * RoomRequest.class.php - model class for table resource_requests
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @property string $id database column
 * @property string $course_id database column
 * @property string $termin_id database column
 * @property string $metadate_id database column
 * @property string $user_id database column
 * @property string $last_modified_by database column
 * @property string $resource_id database column
 * @property string|null $category_id database column
 * @property string|null $comment database column
 * @property string|null $reply_comment database column
 * @property string $reply_recipients database column
 * @property int $closed database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property int $begin database column
 * @property int $end database column
 * @property int $preparation_time database column
 * @property int $marked database column
 * @property SimpleORMapCollection|ResourceRequestProperty[] $properties has_many ResourceRequestProperty
 * @property SimpleORMapCollection|ResourceRequestAppointment[] $appointments has_many ResourceRequestAppointment
 * @property Room $room belongs_to Room
 * @property Resource $resource belongs_to Resource
 * @property ResourceCategory|null $category belongs_to ResourceCategory
 * @property User $user belongs_to User
 * @property User $last_modifier belongs_to User
 * @property Course $course belongs_to Course
 * @property SeminarCycleDate $cycle belongs_to SeminarCycleDate
 * @property CourseDate $date belongs_to CourseDate
 * @property mixed $seats additional field
 * @property mixed $room_type additional field
 * @property mixed $booking_plan_is_public additional field
 */
class RoomRequest extends ResourceRequest
{
    protected static function configure($config = [])
    {
        $config['belongs_to']['room'] = [
            'class_name'  => Room::class,
            'foreign_key' => 'resource_id',
            'assoc_func'  => 'find'
        ];

        $required_properties = [
            'seats',
            'room_type',
            'booking_plan_is_public'
        ];

        $config['additional_fields'] = [];
        foreach ($required_properties as $property) {
            $config['additional_fields'][$property] = [
                'get' => 'getProperty',
                'set' => 'setProperty'
            ];
        }

        parent::configure($config);

    }

    public function checkOpen($also_change = false)
    {
        $db              = DBManager::get();
        $existing_assign = false;
        //a request for a date is easy...
        if ($this->termin_id) {
            $query           = sprintf("SELECT id FROM resource_bookings WHERE range_id = %s ", $db->quote($this->termin_id));
            $existing_assign = $db->query($query)->fetchColumn();
            //metadate request
        } elseif ($this->metadate_id) {
            $query = sprintf("SELECT count(termin_id)=count(resource_bookings.id) FROM termine LEFT JOIN resource_bookings ON(termin_id=resource_bookings.range_id)
                    WHERE metadate_id=%s", $db->quote($this->metadate_id));
            //seminar request
        } else {
            $query = sprintf("SELECT count(termin_id)=count(resource_bookings.id) FROM termine LEFT JOIN resource_bookings ON(termin_id=resource_bookings.range_id)
                    WHERE range_id='%s' AND date_typ IN" . getPresenceTypeClause(), $this->course_id);
        }
        if ($query) {
            $existing_assign = $db->query($query)->fetchColumn();
        }

        if ($existing_assign && $also_change) {
            $this->closed = 1;
            $this->store();
        }
        return (bool)$existing_assign;
    }
}
