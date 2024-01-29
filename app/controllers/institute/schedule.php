<?php
class Institute_ScheduleController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (Navigation::hasItem('/course/main')) {
            Navigation::activateItem('/course/main');
        }

        if (!$GLOBALS['perm']->have_studip_perm('autor', Context::getId())) {
            throw new AccessDeniedException();
        }
    }

    public function index_action($institute_id)
    {
        PageLayout::setTitle(_('Veranstaltungs-Stundenplan'));

        if (Navigation::hasItem('/course/main/schedule')) {
            Navigation::activateItem('/course/main/schedule');
        }

        $semester = null;
        if (Request::submitted('semester_id')) {
            $semester = Semester::find(Request::option('semester_id'));
        } else {
            $semester = Semester::findCurrent();
        }

        $extra_params = [];
        if ($semester) {
            $extra_params['semester_id'] = $semester->id;
        }

        $sidebar = Sidebar::get();
        $semester_widget = new SemesterSelectorWidget($this->url_for('institute/schedule/index/' . $institute_id));
        if ($semester) {
            $semester_widget->setSelection($semester->id);
        }
        $sidebar->addWidget($semester_widget);

        $calendar_settings  = $GLOBALS['user']->cfg->CALENDAR_SETTINGS ?? [];
        $week_slot_duration = \Studip\Calendar\Helper::getCalendarSlotDuration('week');

        $this->fullcalendar = \Studip\Fullcalendar::create(
            _('Veranstaltungs-Stundenplan'),
            [
                'minTime'    => '08:00',
                'maxTime'    => '20:00',
                'allDaySlot' => false,
                'header'     => [
                    'left'  => '',
                    'right' => ''
                ],
                'views' => [
                    'timeGridWeek' => [
                        'columnHeaderFormat' => ['weekday' => 'long'],
                        'weekends'           => $calendar_settings['type_week'] === 'LONG',
                        'slotDuration'       => $week_slot_duration
                    ]
                ],
                'defaultView' => 'timeGridWeek',
                'defaultDate' => date('Y-m-d'),
                'timeGridEventMinHeight' => 20,
                'eventSources' => [
                    [
                        'url'         => $this->url_for('institute/schedule/data/' . $institute_id),
                        'method'      => 'GET',
                        'extraParams' => $extra_params
                    ]
                ]
            ]
        );
    }


    public function data_action($institute_id)
    {
        //Fullcalendar sets the week time range in which to put the course dates
        //of the semester. Therefore, start and end are handled in here.
        $begin = Request::getDateTime('start', \DateTime::RFC3339);
        $end = Request::getDateTime('end', \DateTime::RFC3339);
        if (!($begin instanceof DateTime) || !($end instanceof DateTime)) {
            //No time range specified.
            throw new InvalidArgumentException('Invalid parameters!');
        }

        $semester_id = Request::option('semester_id');
        $semester = Semester::find($semester_id);
        if (!$semester) {
            $this->render_json([]);
            return;
        }

        //Get all regular course dates for that semester:
        $cycle_dates = SeminarCycleDate::findBySql(
            'JOIN `termine` USING (`metadate_id`)
             JOIN `seminare` USING (`seminar_id`)
             JOIN `seminar_inst` USING (`seminar_id`)
             WHERE `seminar_inst`.`institut_id` = :institute_id
               AND (
                 `termine`.`date` BETWEEN :begin AND :end
                 OR `termine`.`end_time` BETWEEN :begin AND :end
               )
             GROUP BY `metadate_id`',
            [
                'institute_id' => $institute_id,
                'begin'        => $semester->beginn,
                'end'          => $semester->ende
            ]
        );

        if (!$cycle_dates) {
            $this->render_json([]);
            return;
        }

        foreach ($cycle_dates as $cycle_date) {
            //Calculate a fake begin and end that lies in the week
            //fullcalendar has specified.
            $fake_begin = clone $begin;
            $fake_end = clone $begin;
            if ($cycle_date->weekday > 1) {
                $fake_begin = $fake_begin->add(new DateInterval('P' . ($cycle_date->weekday - 1) . 'D'));
                $fake_end = $fake_end->add(new DateInterval('P' . ($cycle_date->weekday - 1) . 'D'));
            }
            $start_time_parts = explode(':', $cycle_date->start_time);
            $end_time_parts = explode(':', $cycle_date->end_time);
            $fake_begin->setTime(
                (int) $start_time_parts[0],
                (int) $start_time_parts[1],
                (int) $start_time_parts[2]
            );
            $fake_end->setTime(
                (int) $end_time_parts[0],
                (int) $end_time_parts[1],
                (int) $end_time_parts[2]
            );

            //Get the course colour:
            $course_membership = CourseMember::findOneBySQL(
                'seminar_id = :course_id AND user_id = :user_id',
                [
                    'course_id' => $cycle_date->seminar_id,
                    'user_id' => $GLOBALS['user']->id
                ]
            );
            $event_classes = [];
            if ($course_membership) {
                $event_classes[] = sprintf('course-color-%u', $course_membership->gruppe);
            }

            $event = new \Studip\Calendar\EventData(
                $fake_begin,
                $fake_end,
                $cycle_date->course->getFullName(),
                $event_classes,
                '',
                '',
                false,
                'SeminarCycleDate',
                $cycle_date->id,
                '',
                '',
                'course',
                $cycle_date->seminar_id,
                [
                    'show' => $this->url_for('course/details', ['cid' => $cycle_date->seminar_id, 'link_to_course' => '1'])
                ]
            );

            $result[] = $event->toFullcalendarEvent();
        }

        $this->render_json($result);
    }
}
