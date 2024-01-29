<?php

namespace Studip\Calendar;

class Helper
{
    /**
     * Retrieves the time slot duration in the calendar for a specified calendar type
     * and either the current user or a specific user.
     *
     * @param string $calendar_type The calendar type for which to retrieve the slot duration.
     *     Valid values: 'week', 'day', 'week_group' (week group calendar), 'week_day' (day group calendar).
     *     Defaults to 'week'.
     * @param string $user_id The user for which to retrieve the slot duration. Defaults to an
     *     empty string which then in turn means the current users slot duration is retrieved.
     *
     * @return string The slot duration as a time string in the form HH:MM:SS.
     */
    public static function getCalendarSlotDuration(string $calendar_type = 'week', string $user_id = '') : string
    {
        $default_slot_duration = '00:30:00';

        $user_config = new \UserConfig($user_id ?: $GLOBALS['user']->id);
        $calendar_settings = $user_config->CALENDAR_SETTINGS;

        if (
            $calendar_type === 'week'
            && !empty($calendar_settings['step_week'])
        ) {
            $step_week = (int) $calendar_settings['step_week'];
            $hours = floor($step_week / 3600);
            $minutes = round(($step_week - $hours * 3600) / 60);
            return sprintf('%1$02u:%2$02u:00', $hours, $minutes);
        } elseif (
            $calendar_type === 'day'
            && !empty($calendar_settings['step_day'])
        ) {
            $step_day = (int) $calendar_settings['step_day'];
            $hours = floor($step_day / 3600);
            $minutes = round(($step_day - $hours * 3600) / 60);
            return sprintf('%1$02u:%2$02u:00', $hours, $minutes);
        } elseif (
            $calendar_type === 'week_group'
            && !empty($calendar_settings['step_week_group'])
        ) {
            $step_week = (int) $calendar_settings['step_week_group'];
            $hours = floor($step_week / 3600);
            $minutes = round(($step_week - $hours * 3600) / 60);
            return sprintf('%1$02u:%2$02u:00', $hours, $minutes);
        } elseif (
            $calendar_type === 'day_group'
            && !empty($calendar_settings['step_day_group'])
        ) {
            $step_day = (int) $calendar_settings['step_day_group'];
            $hours = floor($step_day / 3600);
            $minutes = round(($step_day - $hours * 3600) / 60);
            return sprintf('%1$02u:%2$02u:00', $hours, $minutes);
        }

        // An unknown slot type or no appropriate match before:
        // Return the default duration.
        return $default_slot_duration;
    }


    /**
     * Retrieves the default calendar date by various methods.
     *
     * @return \DateTime The default date for the calendar.
     *     This defaults to the current date if no other date
     *     can be retrieved.
     */
    public static function getDefaultCalendarDate() : \DateTime
    {
        $default_date = new \DateTime();
        if (\Request::submitted('date')) {
            $date = \Request::getDateTime('date', 'Y-m-d');
            if ($date instanceof \DateTime) {
                $default_date = $date;
                //Update the session value:
                $_SESSION['calendar_date'] = $default_date->format('Y-m-d');
            }
        } elseif (\Request::submitted('semester_id')) {
            //A semester-ID is set, but no specific date that would override it.
            //Use the first lecture week of the semester as default date.
            $semester_id = \Request::option('semester_id');
            $semester = \Semester::find($semester_id);
            if ($semester) {
                $default_date->setTimestamp($semester->vorles_beginn);
                //Update the session value:
                $_SESSION['calendar_date'] = $default_date->format('Y-m-d');
            }
        } elseif (!empty($_SESSION['calendar_date'])) {
            $date = \DateTime::createFromFormat(
                'Y-m-d',
                $_SESSION['calendar_date'],
                $default_date->getTimezone()
            );
            if ($date instanceof \DateTime) {
                $default_date = $date;
            }
        }
        $default_date->setTime(0,0,0);

        return $default_date;
    }
}
