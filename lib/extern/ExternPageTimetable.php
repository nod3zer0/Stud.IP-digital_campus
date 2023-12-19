<?php
/**
 * ExternPageTimetable.php - Class to provide course dates
 * to show as a timetable.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.4
 */

class ExternPageTimetable extends ExternPage
{
    /**
     * @var int The start time
     */
    private $start_time = 0;

    /**
     * @var int The end time
     */
    private $end_time = 0;

    /**
     * @see ExternPage::getSortFields()
     * @return array Array of field names as keys and spoken names as values.
     */
    public function getSortFields() : array
    {
        return [];
    }

    public function getConfigFields(bool $as_array = false)
    {
        $fields = '
            language       option,
            date,
            date_offset    option,
            range_count    int,
            time_range     option,
            participating  int,
            categories     optionArray,
            studyareas     optionArray,
            scope_kids     int,
            institutes     optionArray,
            sublevels      int,
            semtypes       optionArray,
            event_types    optionArray,
            escaping
        ';
        return $as_array ? self::argsToArray($fields) : $fields;
    }

    public function getAllowedRequestParams(bool $as_array = false)
    {
        $params = [
            'language',
            'date',
            'event_types'
        ];
        return $as_array ? $params : implode(',', $params);
    }

    public function getMarkersContents(): array
    {
        return $this->getContent();
    }

    /**
     * Returns select-options for time ranges.
     *
     * @return array Array with select-options.
     */
    public function getTimeRangeOptions(): array
    {
        return [
            'days'      => _('Tage'),
            'weeks'     => _('Wochen'),
            'months'    => _('Monate'),
            'years'     => _('Jahre'),
            'semesters' => _('Semester')
        ];
    }

    /**
     * Returns select-options for date offsets.
     *
     * @return array Array with select-options.
     */
    public function getDateOffsetOptions(): array
    {
        return [
            'start_date'    => _('Angegebenes Startdatum'),
            'current_date'  => _('Aktuelles Datum'),
            'next_semester' => _('Nächstes Semester'),
            'next_week'     => _('Nächste Woche'),
            'next_month'    => _('Nächster Monat'),
            'next_year'     => _('Nächstes Jahr')
        ];
    }

    private function getDates(): SimpleCollection
    {
        $params = [];
        $query = "
            SELECT
                `termine`.*
            FROM
                `termine`
                LEFT JOIN `seminare`
                    ON `termine`.`range_id` = `seminare`.`Seminar_id`
                LEFT JOIN `seminar_sem_tree`
                    ON `seminare`.`Seminar_id` = `seminar_sem_tree`.`seminar_id`
                LEFT JOIN `seminar_inst`
                    ON `seminare`.`Seminar_id` = `seminar_inst`.`Seminar_id`";
        $query .= "
            WHERE (`termine`.`date` >= :start AND `termine`.`date` <= :end) "
            . $this->getEventTypeSQL($params)
            . $this->getScopesSQL($params, $this->studyareas, (bool) $this->scope_kids)
            . $this->getInstitutesSQL($params)
            . $this->getSemtypesSQL($params) .
            ' GROUP BY `termin_id` ORDER BY `date` ASC';
        $params[':start'] = $this->getStartTime();
        $params[':end']   = $this->getEndTime();
        return SimpleCollection::createFromArray(DBManager::get()->fetchAll(
            $query, $params, 'CourseDate::buildExisting'));
    }

    private function getEventTypeSQL(&$params): string
    {
        if ($this->event_types) {
            $params[':event_types'] = $this->event_types;
            return ' AND `termine`.`date_typ` IN (:event_types) ';
        }
        return '';
    }

    private function getSemtypesSQL(&$params): string
    {
        if ($this->semtypes) {
            $params[':semtypes'] = $this->semtypes;
            return ' AND `seminare`.`status` IN (:semtypes) ';
        }
        return '';
    }

    /**
     * Calculates the start time from given config parameters.
     *
     * @return int The start time as unix timestamp.
     */
    private function getStartTime(): int
    {
        if ($this->start_time !== 0) {
            return $this->start_time;
        }
        $time = new DateTime();
        switch ($this->date_offset) {
            case 'start_date':
                $time = DateTime::createFromFormat('d.m.Y', $this->date);
                break;
            case 'current_semester':
                $semester = Semester::findCurrent();
                $time->setTimestamp($semester->beginn);
            case 'next_semester':
                $semester = Semester::findNext();
                $time->setTimestamp($semester->beginn);
                break;
            case 'next_week':
                $time->modify('monday');
                break;
            case 'next_month':
                $time->modify('first day of next month');
                break;
            case 'next_year':
                $time->modify('first day of next year');
        }
        $this->start_time = $time->setTime(0, 0)->getTimestamp();
        return $this->start_time;
    }

    /**
     * Calculates the end time from config parameters.
     *
     * @return int The end time as unix timestamp.
     */
    private function getEndTime(): int
    {
        if ($this->end_time !== 0) {
            return $this->end_time;
        }
        $time = new DateTime();
        $start_time = $this->getStartTime();
        if ($this->time_range === 'semester') {
            $semester = Semester::findByTimestamp($start_time);
            $i = $this->range_count;
            while (--$i > 0) {
                $next_semester = Semester::findNext($semester->beginn);
                if (!is_null($next_semester)) {
                    $semester = $next_semester;
                }
            }
            $time->setTimestamp($semester->end);
        } else {
            $time->setTimestamp($start_time);
            $count = $this->range_count - 1;
            switch ($this->time_range) {
                case 'days':
                    $time->modify(sprintf('+%s days', $count));
                    break;
                case 'weeks':
                    $time->modify(sprintf('Sunday +%s weeks', $count));
                    break;
                case 'years':
                    $time->modify('31 dec');
                    $time->modify(sprintf('+%s years', $count));
            }
        }
        $this->end_time = $time->setTime(23, 59)->getTimestamp();
        return $this->end_time;
    }

    protected function getContent()
    {
        $count = 0;
        foreach ($this->getDates() as $date) {
            $day = new DateTime();
            $day->setTimestamp($date->date)->setTime(0, 0);
            $day_timestamp = $day->getTimestamp();
            $date_content[$day_timestamp]['DAY'] = $day_timestamp;
            $date_content[$day_timestamp]['DATES'][] = $this->getDateContent($date);
            $count++;
        }
        return [
            'START'         => $this->start_time,
            'END'           => $this->end_time,
            'COUNT_DATES'   => $count,
            'GROUPED_DATES' => $date_content
        ];
    }

    private function getDateContent(CourseDate $date)
    {
        $date_content = [
            'COURSE'      => $this->getCourseContent($date),
            'START'       => $date->date,
            'END'         => $date->end_time,
            'ROOM'        => $date->raum,
            'TYPE'        => $GLOBALS['TERMIN_TYP'][$date->date_typ]['name'],
            'LECTURERS'   => $this->getLecturers($date),
            'TOPICS'      => $this->getTopics($date),
            'BOOKED_ROOM' => $this->getBookedRoomData($date)
        ];
        return $date_content;
    }

    private function getCourseContent(CourseDate $date): array
    {
        return [
            'TITLE'        => $date->course->name,
            'FULLTITLE'    => $date->course->getFullname(),
            'SUBTITLE'     => $date->course->untertitel,
            'NUMBER'       => $date->course->veranstaltungsnummer,
            'SEMESTER'     => $date->course->getFullname('sem-duration-name'),
            'AVATAR_URL'   => $date->course->getItemAvatarURL(),
            'INFO_URL'     => $date->course->getItemURL(),
            'SEMTYPENAME'  => $GLOBALS['SEM_TYPE'][$date->course->status]['name'],
            'SEMCLASSNAME' =>
                $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$date->course->status]['class']]['name'],
            'ID'           => $date->course->id,
            'LECTURERS'    => $this->getContentMembers($date->course, 'dozent')
        ];
    }

    private function getLecturers(CourseDate $date): array
    {
        $lecturers_content = [];
        foreach ($date->dozenten as $lecturer) {
            $lecturers_content[] = [
                'LASTNAME'  => $lecturer->nachname,
                'FIRSTNAME' => $lecturer->vorname,
                'FULLNAME'  => $lecturer->getFullname(),
                'ID'        => $lecturer->id,
                'EMAIL'     => $lecturer->email
            ];

        }
        return $lecturers_content;
    }

    private function getTopics(CourseDate $date): array
    {
        $topics_content = [];
        foreach ($date->topics as $topic) {
            $topics_content[] = [
                'TITLE'       => $topic->title,
                'DESCRIPTION' => $topic->description
            ];
        }
        return $topics_content;
    }

    private function getBookedRoomData(CourseDate $date): array
    {
        if ($date->room_booking) {
            return [
                'NAME' => $date->room_booking->resource->name,
                'ID'   => $date->room_booking->resource->id
            ];
        }
        return [];
    }

}
