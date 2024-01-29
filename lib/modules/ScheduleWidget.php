<?php
/*
 * This class displays a seminar-schedule for
 * users on a seminar-based view and for admins on an institute based view
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Till Glöggler <tgloeggl@uos.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class ScheduleWidget extends CorePlugin implements PortalPlugin
{
    /**
     * Returns the name of the plugin/widget.
     *
     * @return String containing the name
     */
    public function getPluginName()
    {
        return _('Mein Stundenplan');
    }

    public function getMetadata()
    {
        return [
            'description' => _('Mit diesem Widget haben Sie eine Übersicht Ihres aktuellen Stundenplans.')
        ];
    }

    /**
     * Return the template for the widget.
     *
     * @return Flexi_PhpTemplate The template containing the widget contents
     */
    public function getPortalTemplate()
    {
        $week_slot_duration = \Studip\Calendar\Helper::getCalendarSlotDuration('week');
        $calendar_settings = $GLOBALS['user']->cfg->CALENDAR_SETTINGS ?? [];

        $semester = Semester::findCurrent();
        $fullcalendar = \Studip\Fullcalendar::create(
            '',
            [
                'minTime' => '08:00',
                'maxTime' => '20:00',
                'allDaySlot' => false,
                'header' => [
                    'left' => '',
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
                        'url' => URLHelper::getURL('dispatch.php/calendar/calendar/schedule_data'),
                        'method' => 'GET',
                        'extraParams' => [
                            'semester_id' => $semester->id,
                            'full_semester_time_range' => false
                        ]
                    ]
                ]
            ]
        );

        $template = $GLOBALS['template_factory']->open('shared/string');
        $template->content = $fullcalendar;

        return $template;
    }
}
