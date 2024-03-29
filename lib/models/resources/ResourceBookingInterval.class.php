<?php

/**
 * ResourceBookingInterval.class.php - model class for storing
 * all resource bookings time intervals, including those for
 * repetitions.
 *
 * The ResourceBookingEvent class contains all
 * time intervals of all resources where they are
 * assigned.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.1
 *
 * @property string $id alias column for interval_id
 * @property string $interval_id database column
 * @property string $resource_id database column
 * @property string $booking_id database column
 * @property int $begin database column
 * @property int $end database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $takes_place database column
 * @property ResourceBooking $booking belongs_to ResourceBooking
 * @property Resource $resource belongs_to Resource
 */
class ResourceBookingInterval extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_booking_intervals';

        $config['belongs_to']['booking'] = [
            'class_name' => ResourceBooking::class,
            'foreign_key' => 'booking_id',
            'assoc_func' => 'find'
        ];

        $config['belongs_to']['resource'] = [
            'class_name' => Resource::class,
            'foreign_key' => 'resource_id',
            'assoc_func' => 'find'
        ];

        parent::configure($config);
    }

    /**
     * Creates resource booking interval entries by checking the
     * values of a specified ResourceBooking object.
     *
     * @param ResourceBooking $booking The resource booking object
     *     from which interval entries shall be generated.
     *
     * @return True, if all intervals have been created successfully,
     *     false otherwise.
     */
    public static function createFromBooking(ResourceBooking $booking)
    {
        $time_intervals = $booking->calculateTimeIntervals();
        foreach ($time_intervals as $time_interval) {
            //Check if the time interval already exists:
            $interval_exists = ResourceBookingInterval::countBySql(
                'booking_id = :booking_id AND resource_id = :resource_id
                AND begin = :begin AND end = :end',
                [
                    'booking_id' => $booking->id,
                    'resource_id' => $booking->resource_id,
                    'begin' => $time_interval['begin'],
                    'end' => $time_interval['end']
                ]
            ) > 0;
            if (!$interval_exists) {
                $interval = new ResourceBookingInterval();
                $interval->booking_id = $booking->id;
                $interval->resource_id = $booking->resource_id;
                $interval->begin = $time_interval['begin'];
                $interval->end = $time_interval['end'];
                if (!$interval->store()) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Converts this ResourceBookingInterval object to a DateInterval object.
     *
     * @return DateInterval A DateInterval representing the time interval
     *     of this ResourceBookingInterval object.
     */
    public function toDateInterval()
    {
        $begin = new DateTime();
        $begin->setTimestamp($this->begin);
        $end = new DateTime();
        $end->setTimestamp($this->end);

        return $begin->diff($end);
    }

    public function __toString()
    {
        if (date('Ymd', $this->begin) == date('Ymd', $this->end)) {
            return strftime('%a %x %R', $this->begin) . ' - ' . strftime('%R', $this->end);
        } else {
            return strftime('%a %x %R', $this->begin)
                . ' - '
                . strftime('%a %x %R', $this->end);
        }
    }
}
