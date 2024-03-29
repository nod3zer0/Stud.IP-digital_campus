<?php


//Do not remove! This is still needed for the toString method!
require_once 'lib/dates.inc.php';


/**
 * SeminarCycleDate.class.php
 * model class for table seminar_cycle_dates
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.0
 *
 * @property string $id alias column for metadate_id
 * @property string $metadate_id database column
 * @property string $seminar_id database column
 * @property string $start_time database column
 * @property string $end_time database column
 * @property int $weekday database column
 * @property string $description database column
 * @property float $sws database column
 * @property int $cycle database column
 * @property int $week_offset database column
 * @property int|null $end_offset database column
 * @property int $sorter database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|RoomRequest[] $room_requests has_many RoomRequest
 * @property SimpleORMapCollection|CourseDate[] $dates has_many CourseDate
 * @property SimpleORMapCollection|CourseExDate[] $exdates has_many CourseExDate
 * @property Course $course belongs_to Course
 * @property mixed $start_hour additional field
 * @property mixed $start_minute additional field
 * @property mixed $end_hour additional field
 * @property mixed $end_minute additional field
 * @property-read mixed $is_visible additional field
 */
class SeminarCycleDate extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param Array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminar_cycle_dates';
        $config['belongs_to']['course'] = array(
            'class_name' => Course::class,
            'foreign_key' => 'seminar_id',
        );
        $config['has_many']['room_requests'] = [
            'class_name' => RoomRequest::class,
            'on_store'   => 'store',
            'on_delete'  => 'delete',
            'assoc_func' => 'findByMetadate_id'
        ];
        $config['has_many']['dates'] = [
            'class_name' => CourseDate::class,
            'on_delete'  => 'delete',
            'on_store'   => 'store',
            'order_by'   => 'ORDER BY date'
        ];

        $config['has_many']['exdates'] = [
            'class_name' => CourseExDate::class,
            'on_delete'  => 'delete',
            'on_store'   => 'store',
            'order_by'   => 'ORDER BY date'
        ];

        $config['additional_fields']['start_hour'] = ['get' => 'getTimeFraction', 'set' => 'setTimeFraction'];
        $config['additional_fields']['start_minute'] = ['get' => 'getTimeFraction', 'set' => 'setTimeFraction'];
        $config['additional_fields']['end_hour'] = ['get' => 'getTimeFraction', 'set' => 'setTimeFraction'];
        $config['additional_fields']['end_minute'] = ['get' => 'getTimeFraction', 'set' => 'setTimeFraction'];
        $config['additional_fields']['is_visible'] = ['get' => 'getIsVisible'];
        parent::configure($config);
    }

    /**
     * returns array of instances of SeminarCycleDates of the given seminar_id
     *
     * @param string seminar_id: selected seminar to search for SeminarCycleDates
     * @return array of instances of SeminarCycleDates of the given seminar_id or
     *               an empty array
     */
    public static function findBySeminar($seminar_id)
    {
        return self::findBySeminar_id($seminar_id, "ORDER BY sorter ASC, weekday ASC, start_time ASC");
    }

    /**
     * return instance of SeminarCycleDates of given termin_id
     *
     * @param string termin_id: selected seminar to search for SeminarCycleDates
     * @return array
     */
    public static function findByTermin($termin_id)
    {
        return self::findOneBySql("metadate_id=(SELECT metadate_id FROM termine WHERE termin_id = ? "
                                  . "UNION SELECT metadate_id FROM ex_termine WHERE termin_id = ? )", [$termin_id, $termin_id]);
    }
    /**
     * Returns the time fraction for a given field.
     *
     * @param String $field Time fraction field
     * @return int the time fraction
     */
    protected function getTimeFraction($field)
    {
        if (in_array($field, ['start_hour', 'start_minute'])) {
            list($start_hour, $start_minute) = explode(':', $this->start_time);
            return (int)$$field;
        }
        if (in_array($field, ['end_hour', 'end_minute'])) {
            list($end_hour, $end_minute) = explode(':', $this->end_time);
            return (int)$$field;
        }

        throw new InvalidArgumentException("Invalid field {$field}");
    }

    /**
     * Sets the time fraction for a given field.
     *
     * @param String $field Time fraction field
     * @param mixed  $value Time fraction value as string or int
     * @return String containing the time fraction
     */
    protected function setTimeFraction($field, $value)
    {
        if ($field == 'start_hour') {
            $this->start_time = sprintf('%02u:%02u:00', $value, $this->start_minute);
            return $this->start_hour;
        }
        if ($field == 'start_minute') {
            $this->start_time = sprintf('%02u:%02u:00', $this->start_hour, $value);
            return $this->start_minute;
        }
        if ($field == 'end_hour') {
            $this->end_time = sprintf('%02u:%02u:00', $value, $this->end_minute);
            return $this->end_hour;
        }
        if ($field == 'end_minute') {
            $this->end_time = sprintf('%02u:%02u:00', $this->end_hour, $value);
            return $this->end_minute;
        }

        throw new InvalidArgumentException("Invalid field {$field}");
    }

    /**
     * Check if there is a least one not cancelled date for this cycle data
     *
     * @return bool   true, if there is at least one not cancelled date
     */
    public function getIsVisible()
    {
        return sizeof($this->dates) ? true : false;
    }

    /**
     * SWS needs special setter to always store a decimal
     *
     * @param number $value
     */
    protected function setSws($value)
    {
        $this->content['sws'] = round(str_replace(',', '.', $value), 1);
    }

    /**
     * returns a string for a date like '3. 9:00s - 10:45' (short and long)
     * or '3. 9:00s - 10:45, , ab der 7. Semesterwoche, (Vorlesung)' with the week of the semester
     * @param format string: "short"|"long"|"full"
     * @return formatted string
     */
    public function toString($format = 'short')
    {
        $template['short'] = '%s. %02s:%02s - %02s:%02s';
        $template['long'] = '%s: %02s:%02s - %02s:%02s, %s';
        $template['full'] = '%s: %02s:%02s - %02s:%02s, ' . _('%s, ab der %s. Semesterwoche');
        if ($this->end_offset) {
            $template['full'] .= ' bis zur %s. Semesterwoche';
        } else {
            $template['full'] .= '%s';
        }
        $template['full'] .= '%s';
        $cycles = [_('wöchentlich'), _('zweiwöchentlich'), _('dreiwöchentlich')];
        $day = getWeekDay($this->weekday, $format == 'short');
        $result = sprintf($template[$format],
            $day,
            $this->start_hour,
            $this->start_minute,
            $this->end_hour,
            $this->end_minute,
            $cycles[(int)$this->cycle],
            $this->week_offset + 1,
            $this->end_offset ? $this->end_offset: '',
            $this->description ? ' (' . $this->description . ')' : '');
        return $result;
    }

    /**
     * returns an sorted array with all dates and exdates for the cycledate entry
     * @return array of instances of dates or exdates
     */
    public function getAllDates()
    {
        $dates = [];
        foreach ($this->exdates as $date) {
            $dates[] = $date;
        }
        foreach ($this->dates as $date) {
            $dates[] = $date;
        }

        usort($dates, function ($a, $b) {
            return $a->date - $b->date;
        });

        return $dates;
    }

    /**
     * Deletes the cycle.
     *
     * @return int number of affected rows
     */
    public function delete()
    {
        $cycle_info = $this->toString();
        $seminar_id = $this->seminar_id;
        $metadate_id = $this->metadate_id;
        $result = parent::delete();

        if ($result) {
            $stmt = DBManager::get()->prepare('DELETE FROM schedule_seminare WHERE metadate_id = :metadate_id');
            $stmt->execute(['metadate_id' => $metadate_id]);

            StudipLog::log('SEM_DELETE_CYCLE', $seminar_id, null, $cycle_info);
        }

        return $result;
    }

    /**
     * Set date-type for all dates
     * @param $type
     * @return int
     */
    public function setSingleDateType($type)
    {
        $result = 0;
        if (count($this->dates)) {
            foreach($this->dates as $date) {
                $date->date_typ = $type;
                $result += $date->store();
            }
        }
        return $result;
    }

    /**
     * Stores this cycle.
     * @return int number of changed rows
     */
    public function store()
    {
        //create new entry in seminare_cycle_date
        if ($this->isNew()) {
            $result = parent::store();
            if ($result) {
                $course = Course::find($this->seminar_id);
                //create start timestamp
                $new_dates = $this->createTerminSlots($this->calculateTimestamp(
                    $course->start_semester->vorles_beginn,
                    $this->week_offset*7
                ));

                if (!empty($new_dates)) {
                    foreach ($new_dates as $semester_dates) {
                        foreach ($semester_dates['dates'] as $date) {
                            $result += $date->store();
                        }
                    }
                } else {
                    $this->delete();
                    return 0;
                }
                $this->resetRelation("dates");
                StudipLog::log('SEM_ADD_CYCLE', $this->seminar_id, NULL, $this->toString());
                return $result;
            }
            return 0;
        }

        //change existing cycledate, changes also corresponding single dates
        $old_cycle = SeminarCycleDate::find($this->metadate_id);
        if (!parent::store()) {
            return false;
        }

        if ($this->start_time != $old_cycle->start_time
                || $this->end_time != $old_cycle->end_time
                || $old_cycle->weekday != $this->weekday )
        {
            $update_count = $this->updateExistingDates($old_cycle);
        }

        if ($old_cycle->week_offset != $this->week_offset
            || $old_cycle->end_offset != $this->end_offset
            || $old_cycle->cycle != $this->cycle )
        {
            $update_count = $this->generateNewDates();
        }

        StudipLog::log('SEM_CHANGE_CYCLE', $this->seminar_id, NULL,
                $old_cycle->toString() .' -> ' . $this->toString());

        return $update_count;
    }

    private function updateExistingDates($old_cycle)
    {
        $update_count = 0;
        foreach ($this->getAllDates() as $date) {
            // ignore dates in the past
            if ($date->date < time()) {
                continue;
            }

            $tos = $date->date;
            $toe = $date->end_time;
            $day = $this->weekday - $old_cycle->weekday;

            $date->date = mktime(date('G', strtotime($this->start_time)), date('i', strtotime($this->start_time)), 0, date('m', $tos), date('d', $tos), date('Y', $tos)) + $day * 24 * 60 * 60;
            $date->end_time = mktime(date('G', strtotime($this->end_time)), date('i', strtotime($this->end_time)), 0, date('m', $toe), date('d', $toe), date('Y', $toe)) + $day * 24 * 60 * 60;

            if ($date instanceof CourseDate && !is_null($date->room_booking)) {
                //Check if the time range of the date has decreased and did not exceed the
                //boundaries of the existing room booking. In that case, the room booking is shortened.
                if ($date->date < $tos || $date->end_time > $toe) {
                    //The room booking must be deleted.
                    $date->room_booking->delete();
                } else {
                    //The room booking must be shortened.
                    $date->room_booking->begin = $date->date;
                    $date->room_booking->end = $date->end_time;
                    $date->room_booking->store();
                }
            }

            if ($old_cycle->weekday != $this->weekday) {
                $all_holiday = SemesterHoliday::getAll(); // fetch all Holidays
                $holiday_date = false;
                foreach ($all_holiday as $val2) {
                    if (($val2["beginn"] <= $date->date) && ($date->date <= $val2["ende"])) {
                        $holiday_date = true;
                        break;
                    }
                }

                //check for calculatable holidays
                if ($date instanceof CourseDate || $date instanceof CourseExDate) {
                    $holy_type = SemesterHoliday::isHoliday($date->date, false);
                    if ($holy_type["col"] == 3) {
                        $holiday_date = true;
                    }
                }

                if ($holiday_date && $date instanceof CourseDate) {
                    $date->cancelDate();
                } else if (!$holiday_date && $date instanceof CourseExDate) {
                    $date->unCancelDate();
                } else if ($date->isDirty()) {
                    $date->store();
                    $update_count++;
                }
            } else if ($date->isDirty()) {
                $date->store();
                $update_count++;
            }
        }
        $this->resetRelation('dates');
        $this->resetRelation('exdates');
        return $update_count;
    }

    /**
     * Generate any currently missing single dates for this cycle.
     */
    public function generateNewDates()
    {
        $course = Course::find($this->seminar_id);
        $topics = [];
        //collect topics for existing future dates (CourseDate)
        foreach ($this->getAllDates() as $date) {
            if ($date->end_time >= time()) {
                $topics_tmp = CourseTopic::findByTermin_id($date->termin_id);
                if (!empty($topics_tmp)) {
                    $topics[] = $topics_tmp;
                }
            }

            if (is_null($this->end_offset)) {
                // check if seminar is endless
                if ($course->isOpenEnded()) {
                    $last_sem = Semester::findOneBySQL('1 ORDER BY beginn DESC');
                    $end_time_offset = $last_sem->vorles_ende;
                } else {
                    $end_time_offset = $course->end_semester->vorles_ende;
                }
            } else {
                $end_time_offset = $this->calculateTimestamp($course->start_semester->vorles_beginn,
                    ($this->end_offset + 1) * 7
                );
            }

            if ($date->date < $this->calculateTimestamp($course->start_semester->vorles_beginn, $this->week_offset * 7) ||
                $date->date > $end_time_offset
            ) {
                $date->delete();
            }
        }
        //restore for updated singledate entries
        $this->resetRelation('dates');
        $this->resetRelation('exdates');

        //create start timestamp
        $new_dates = $this->createTerminSlots($this->calculateTimestamp(
            $course->start_semester->vorles_beginn,
            $this->week_offset*7
        ));

        $update_count = 0;

        foreach ($new_dates as $semester_dates) {
            //update or create singeldate entries
            foreach ($semester_dates['dates'] as $date) {
                if ($date instanceof CourseDate && $date->date >= time() && count($topics) > 0) {
                    $date->topics = array_shift($topics);
                }
                if ($date->store()) {
                    $update_count++;
                }
            }

            //delete unnecessary singeldate entries
            foreach ($semester_dates['dates_to_delete'] as $date) {
                $date->delete();
            }
        }
        return $update_count;
    }

    /**
     * generate single date objects for one cycle and all semester, existing dates are merged in
     *
     * @param startAfterTimeStamp => int timestamp to override semester start
     * @return array array of arrays, for each semester id  an array of two arrays of SingleDate objects: 'dates' => all new and surviving dates, 'dates_to_delete' => obsolete dates
     */
    private function createTerminSlots($startAfterTimeStamp = 0)
    {
        $course = Course::find($this->seminar_id);
        $ret = [];

        if ($startAfterTimeStamp == 0 || $startAfterTimeStamp < $course->start_semester->vorles_beginn) {
            $startAfterTimeStamp = $course->start_semester->vorles_beginn;
        }

        // check if cycle has a fix end (end_offset == null -> endless)
        if (is_null($this->end_offset)) {
            // check if seminar is endless
            if ($course->isOpenEnded()) {
                $last_sem = Semester::findOneBySQL('1 ORDER BY beginn DESC');
                $sem_end = $last_sem->vorles_ende;
            } else {
                $sem_end = $course->end_semester->vorles_ende;
            }
        } else {
            $sem_end = $this->calculateTimestamp($course->start_semester->vorles_beginn, ($this->end_offset + 1) * 7);
        }

        $semester = Semester::findBySQL('beginn <= :ende AND ende >= :start',
                ['start' => $startAfterTimeStamp, 'ende' => $sem_end]);

        foreach ($semester as $val) {
                $ret[$val['semester_id']] = $this->createSemesterTerminSlots($val['vorles_beginn'], $val['vorles_ende'], $startAfterTimeStamp);
        }
        return $ret;
    }


    /**
     * generate single date objects for one cycle and one semester, existing dates are merged in
     *
     * @param string cycle id
     * @param int    timestamp of semester start
     * @param int    timestamp of semester end
     * @param int    alternative timestamp to start from
     * @return array returns an array of two arrays of SingleDate objects: 'dates' => all new and surviving dates, 'dates_to_delete' => obsolete dates
     */
    private function createSemesterTerminSlots($sem_begin, $sem_end, $startAfterTimeStamp)
    {

        $dates = [];
        $dates_to_delete = [];

        // The currently existing singledates for the by metadate_id denoted  regular time-entry
        //$existingSingleDates =& $this->cycles[$metadate_id]->getSingleDates();
        $all_dates = $this->getAllDates();
        $existingSingleDates =& $all_dates;

        $start_woche = $this->week_offset;
        $turnus_offset = 0;

        // This variable is used to check if a given singledate shall be created in a bi-weekly seminar.
        if ($start_woche == -1) {
            $start_woche = 0;
        }
        $week = 0;

        // get the first presence date after sem_begin
        $day_of_week = date('l', strtotime('Sunday + ' . $this->weekday . ' days'));
        $stamp = strtotime('this week ' . $day_of_week, max($sem_begin, $startAfterTimeStamp));

        $start = explode(':', $this->start_time);

        $start_time = mktime(
            (int)$start[0],                                     // Hour
            (int)$start[1],                                     // Minute
            0,                                                  // Second
            date("n", $stamp),                                  // Month
            date("j", $stamp),                                  // Day
            date("Y", $stamp));                                 // Year

        $end = explode(':', $this->end_time);
        $end_time = mktime(
            (int)$end[0],                                       // Hour
            (int)$end[1],                                       // Minute
            0,                                                  // Second
            date("n", $stamp),                                  // Month
            date("j", $stamp),                                  // Day
            date("Y", $stamp));                                 // Year

        $course = Course::find($this->seminar_id);

        // check if cycle has a fix end (end_offset == null -> endless)
        if (is_null($this->end_offset)) {
            // check if seminar is endless
            if ($course->isOpenEnded()) {
                $last_sem = Semester::findOneBySQL('1 ORDER BY beginn DESC');
                $end_time_offset = $last_sem->vorles_ende;
            } else {
                $end_time_offset = $course->end_semester->vorles_ende;
            }
        } else {
            $end_time_offset = $this->calculateTimestamp($course->start_semester->vorles_beginn, ($this->end_offset + 1) * 7);
        }

        // loop through all possible singledates for this regular time-entry
        do {

            // if dateExists is true, the singledate will not be created. Default is of course to create the singledate
            $dateExists = false;

            // do not create singledates if they are earlier than the semester start
            if ($end_time < $sem_begin) {
                $dateExists = true;
                $turnus_offset = 1;
            }

            /*
             * We only create dates, which do not already exist, so we do not overwrite existing dates.
             *
             * Additionally, we delete singledates which are not needed any more (bi-weekly, changed start-week, etc.)
             */
            $date_values['range_id'] = $this->seminar_id;
            $date_values['autor_id'] = $GLOBALS['user']->id;
            $date_values['metadate_id'] = $this->metadate_id;
            foreach ($existingSingleDates as $key => $val) {
                // take only the singledate into account, that maps the current timepoint
                // only compare the week and year, because dates in the past may differ on time or day
                if ($start_time > $startAfterTimeStamp && date('W Y', $val->date) == date('W Y', $start_time)) {
                    $dateExists = true;
                    if (isset($existingSingleDates[$key])) {
                        $dates[] = $val;
                    }
                }
            }

            if (!$dateExists) {
                $termin = new CourseDate();

                $all_holiday = SemesterHoliday::getAll(); // fetch all Holidays
                foreach ($all_holiday as $val2) {
                    if (($val2["beginn"] <= $start_time) && ($start_time <= $val2["ende"])) {
                        $termin = new CourseExDate();
                        break;
                    }
                }

                //check for calculatable holidays
                if ($termin instanceof CourseDate) {
                    $holy_type = SemesterHoliday::isHoliday($start_time, false);
                    if (!is_bool($holy_type) && $holy_type['col'] == 3) {
                        $termin = new CourseExDate();
                    }
                }
                $date_values['date'] = $start_time;
                $date_values['end_time'] = $end_time;
                $date_values['date_type'] = 1;
                $termin->setData($date_values);

                $dates[] = $termin;
            }

            //inc the week, create timestamps for the next singledate
            $start_time = strtotime('+ 1 week', $start_time);
            $end_time = strtotime('+ 1 week', $end_time);
            $week++;

        } while ($end_time < $sem_end && $end_time < $end_time_offset);

        //calulate trurnus
        if ($this->cycle != 0) {
            return $this->calculateTurnusDates($dates, $turnus_offset);
        }
        return ['dates' => $dates, 'dates_to_delete' => $dates_to_delete];
    }


    /**
     * Calculate turnus for singledate entries
     *
     * @param array $dates
     * @param int $turnus_offset correction for turnus calculation if first date is not within semester
     * @return array
     */
    public function calculateTurnusDates($dates, $turnus_offset)
    {
        $week_count = 0 + $turnus_offset;
        $dates_to_store = [];
        $dates_to_delete = [];
        foreach ($dates as $date) {

            if ($this->cycle == 1 && $week_count % 2 != 0 && $week_count > 0) {
                if (!$date->isNew()) {
                    $dates_to_delete[] = $date;
                }
            } else if ($this->cycle == 2 && $week_count % 3 != 0 && $week_count > 0) {
                if (!$date->isNew()) {
                    $dates_to_delete[] = $date;
                }
            } else {
                $dates_to_store[] = $date;
            }
            $week_count++;
        }
        return ['dates' => $dates_to_store, 'dates_to_delete' => $dates_to_delete];
    }

    /**
     * removes all singleDates which are NOT between $start and $end
     *
     * @param int    timestamp for start
     * @param int    timestamp for end
     * @param string seminar_id
     */
    public static function removeOutRangedSingleDates($start, $end, $seminar_id)
    {
        $query = "SELECT termin_id
                  FROM termine
                  WHERE range_id = ? AND (`date` NOT BETWEEN ? AND ?)
                    AND NOT (metadate_id IS NULL OR metadate_id = '')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_id, $start, $end]);
        $ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        foreach ($ids as $id) {
            $termin = new SingleDate($id);
            $termin->delete();
            unset($termin);
        }

        if (count($ids) > 0) {
            // remove all assigns for the dates in question
            $query = "SELECT id FROM resource_bookings WHERE range_id IN (?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$ids]);

            $booking_ids = $statement->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_UNIQUE, 0);

            ResourceBooking::deleteBySql(
                'id IN ( :booking_ids )',
                [
                    'booking_ids' => $booking_ids
                ]
            );
        }

        $query = "DELETE FROM ex_termine
                  WHERE range_id = ? AND (`date` NOT BETWEEN ? AND ?)
                    AND NOT (metadate_id IS NULL OR metadate_id = '')";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_id, $start, $end]);
    }

    /**
     * returns a new timestamp for an given start-timestamp and
     * an amount of days calculated with DateTime-Class
     *
     * @param  int  starttime as timestamp
     * @param  int  amount of days
     * @return int  new timestamp
     */
    private static function calculateTimestamp($base, $days = 0)
    {
        $date = new DateTime();
        $date->setTimestamp($base);
        $date->modify(sprintf('this week monday +%s days', $days));
        return $date->getTimestamp();
    }


    /**
     * This is a helper method for getRoomRequestsForDates and
     * countRoomRequestsForDates which helps at building the
     * SQL query and parameters.
     *
     * @returns array An associative array with two indexes:
     *     - sql: The SQL query
     *     - sql_params: The parameters for the SQL query as associative array.
     */
    protected function buildOpenRequestsForDatesQuery(
        $include_metadate = false,
        $order = ''
    )
    {
        $sql = "closed < '1' AND ";
        $sql_params = [];

        if ($include_metadate) {
            $sql = 'metadate_id = :metadate_id OR ';
        }

        $sql .= 'termin_id IN (
            SELECT termin_id FROM termine
            WHERE metadate_id = :metadate_id
        ) OR id IN (
            SELECT request_id
            FROM resource_request_appointments rra
            INNER JOIN termine
            ON rra.appointment_id = termine.termin_id
            WHERE termine.metadate_id = :metadate_id
        ) ';
        $sql_params['metadate_id'] = $this->id;

        if ($order) {
            $sql .= 'ORDER BY ' . $order;
        }
        return [
            'sql' => $sql,
            'sql_params' => $sql_params
        ];
    }


    /**
     * Returns the open room requests that are associated with this metadate.
     * If the optional parameter $include_metadate is set to false,
     * the requests associated with this metadate are not included
     * in the result.
     *
     * @param bool $include_metadate Whether to include requests associated
     *     with this metadate (true) or not (false). The default is false.
     *
     * @returns ResourceRequest[] The requests for this metadate.
     */
    public function getOpenRequestsForDates(
        $include_metadate = false,
        $order = 'mkdate DESC'
    )
    {
        $data = $this->buildOpenRequestsForDatesQuery($include_metadate, $order);
        return ResourceRequest::findBySql($data['sql'], $data['sql_params']);
    }


    /**
     * Returns the amount of open room requests that are associated
     * with this metadate.
     * The amount of requests is determined by counting requests that are
     * directly associated to this metadate and the requests that are
     * associated with the dates of this metadate.
     * If the optional parameter $include_metadate is set to false,
     * the requests associated with this metadate are not included
     * in the result.
     *
     * @param bool $include_metadate Whether to include requests associated
     *     with this metadate (true) or not (false). The default is false.
     *
     * @returns int The amount of requests for this metadate.
     */
    public function countOpenRequestsForDates($include_metadate = false)
    {
        $data = $this->buildOpenRequestsForDatesQuery($include_metadate);
        return ResourceRequest::countBySql($data['sql'], $data['sql_params']);
    }
}
