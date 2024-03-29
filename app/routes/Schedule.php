<?php
namespace RESTAPI\Routes;

/**
 * @author     André Klaßen <andre.klassen@elan-ev.de>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 6.0.
 *
 * @condition user_id ^[a-f0-9]{1,32}$
 * @condition semester_id ^[a-f0-9]{1,32}$
 */
class Schedule extends \RESTAPI\RouteMap
{
    /**
     * returns schedule for a given user and semester
     *
     * @get /user/:user_id/schedule/:semester_id
     * @get /user/:user_id/schedule
     */
    public function getSchedule($user_id, $semester_id = null)
    {
        if ($user_id !== $GLOBALS['user']->id) {
            $this->error(401);
        }

        $current_semester = isset($semester_id)
            ? \Semester::find($semester_id)
            : \Semester::findCurrent();

        if (!$current_semester) {
            $this->notFound('No such semester.');
        }

        $schedule_settings = \UserConfig::get($user_id)->SCHEDULE_SETTINGS;
        $days = \CalendarScheduleModel::getDisplayedDays($schedule_settings['glb_days']);

        $entries = \CalendarScheduleModel::getEntries(
            $user_id, $current_semester,
            $schedule_settings['glb_start_time'], $schedule_settings['glb_end_time'],
            $days,
            $visible = false
        );

       $json = [];
       foreach ($entries as $number_of_day => $schedule_of_day) {
           $entries = [];
           foreach ($schedule_of_day->entries as $entry) {
               $entries[$entry['id']] = self::entryToJson($entry);
           }
           $json[$number_of_day] = $entries;
       }

       $this->etag(md5(serialize($json)));

       return array_reverse($json, true);
    }


    private static function entryToJson($entry)
    {
        $json = [];
        foreach (['start', 'end', 'content', 'title', 'color', 'type'] as $key) {
            $json[$key] = in_array($key, ['start', 'end'])
                        ? (int) $entry[$key]
                        : $entry[$key];
        }

        return $json;
    }
}
