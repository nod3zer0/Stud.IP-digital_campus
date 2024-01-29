<?php
/**
 * contentbox.php - Calender Contentbox controller
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     calender
 */
class Calendar_ContentboxController extends StudipController
{
    /**
     * Widget controller to produce the formally known show_dates()
     *
     * @param String $range_id range id (or array of range ids) of the news to get displayed
     */
    public function display_action($range_id, $timespan = 604800, $start = null)
    {
        $this->admin = false;
        $this->single = false;
        $this->userRange = false;
        $this->course_range = false;
        $this->termine = [];

        // Fetch time if needed
        $this->start = $start ?: strtotime('today');
        $this->timespan = $timespan;

        // To array fallback of $range_id
        if (!is_array($range_id)) {
            $this->single = true;
            $range_id = [$range_id];
        }

        $this->titles = [];

        foreach ($range_id as $id) {
            switch (get_object_type($id, ['user', 'sem'])) {
                case 'user':
                    $this->parseUser($id);
                    $this->userRange = true;
                    break;
                case 'sem':
                    $this->parseSeminar($id);
                    $this->course_range = true;
                    break;
            }
        }

        // Check permission to edit
        if ($this->single) {
            $this->admin = $range_id[0] === $GLOBALS['user']->id
                || (
                    get_object_type($range_id[0], ['sem']) === 'sem'
                    && $GLOBALS['perm']->have_studip_perm('tutor', $range_id[0])
                );
            // Set range_id
            $this->range_id = $range_id[0];
        }

        // Forge title
        if (!empty($this->termine)) {
            $this->title = sprintf(
                _('Termine für die Zeit vom %s bis zum %s'),
                strftime('%d. %B %Y', $this->start),
                strftime('%d. %B %Y', $this->start + $this->timespan)
            );
        } else {
            $this->title = _('Termine');
        }

        // Check out if we are on a profile
        if ($this->admin) {
            $this->isProfile = $this->single && $this->userRange;
        }
    }

    private function parseSeminar($id)
    {
        $course = Course::find($id);
        $this->termine = $course->getDatesWithExdates()->findBy('end_time', [$this->start, $this->start + $this->timespan], '><');
        foreach ($this->termine as $course_date) {
            if ($this->course_range) {
                //Display only date and time:
                $this->titles[$course_date->id] = $course_date->getFullname('include-room');
            } else {
                //Include the course title:
                $this->titles[$course_date->id] = $course_date->getFullname('verbose');
            }
        }
    }

    private function parseUser($id)
    {
        $begin = new DateTime();
        $begin->setTimestamp($this->start);
        $end = new DateTime();
        $end->setTimestamp($this->start + $this->timespan);

        $this->termine = [];

        if ($GLOBALS['user']->id === $id) {
            //The current user is looking at their dates.
            //Get course dates, too:
            $relevant_courses = Course::findBySQL(
                "JOIN `seminar_user` USING (`seminar_id`)
                WHERE `user_id` = :user_id",
                ['user_id' => $id]
            );
            foreach ($relevant_courses as $course) {
                $course_dates = $course->getDatesWithExdates()->findBy('end_time', [$this->start, $this->start + $this->timespan], '><');
                foreach ($course_dates as $course_date) {
                    $this->titles[$course_date->id] = sprintf(
                        '%1$s: %2$s',
                        $course_date->course->name,
                        $course_date->getFullname()
                    );
                    $this->termine[] = $course_date;
                }
            }
        }

        //Get personal dates:

        $assignments = [];
        if (User::findCurrent()->id === $id) {
            $assignments = CalendarDateAssignment::getEvents($begin, $end, $id);
        } else {
            //Only show public events:
            $assignments = CalendarDateAssignment::getEvents($begin, $end, $id, ['PUBLIC']);
        }
        foreach ($assignments as $assignment) {
            //Exclude events that begin after the given time range:
            if ($assignment->calendar_date->begin > $this->start + $this->timespan) {
                continue;
            }

            $title = '';

            // Adjust title
            if (date('Ymd', $assignment->calendar_date->begin) == date('Ymd')) {
                $title = _('Heute') . date(', H:i', $assignment->calendar_date->begin);
            } else {
                $title = mb_substr(strftime('%a', $assignment->calendar_date->begin), 0, 2);
                $title .= date('. d.m.Y, H:i', $assignment->calendar_date->begin);
            }

            if ($assignment->calendar_date->begin < $assignment->calendar_date->end) {
                if (date('Ymd', $assignment->calendar_date->begin) < date('Ymd', $assignment->calendar_date->end)) {
                    $title .= ' - ' . mb_substr(strftime('%a', $assignment->calendar_date->end), 0, 2);
                    $title .= date('. d.m.Y, H:i', $assignment->calendar_date->end);
                } else {
                    $title .= ' - ' . date('H:i', $assignment->calendar_date->end);
                }
            }

            if ($assignment->calendar_date->title) {
                //Cut the title:
                $tmp_title = mila($assignment->calendar_date->title);
                $title .= ', ' . $tmp_title;
            }
            $this->titles[$assignment->getObjectId()] = $title;

            // Store for view
            $this->termine[] = $assignment;
        }
    }
}
