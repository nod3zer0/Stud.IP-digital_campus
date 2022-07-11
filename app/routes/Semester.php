<?php
namespace RESTAPI\Routes;

/**
 * @author     Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 5.2.
 *
 * @condition semester_id ^[0-9a-f]{1,32}$
 */
class Semester extends \RESTAPI\RouteMap
{
    public function __construct()
    {
        parent::__construct();
        if (!\Request::int('limit')) {
            $this->limit = count(\Semester::getAll());
        }
    }

    /**
     * Returns a list of all semesters.
     *
     * @get /semesters
     * @allow_nobody
     */
    public function getSemesters()
    {
        $semesters = \Semester::getAll();

        // paginate
        $total = count($semesters);
        $semesters = array_slice($semesters, $this->offset, $this->limit);

        $json = [];
        foreach ($semesters as $semester) {
            $url = $this->urlf('/semester/%s', $semester['semester_id']);
            $json[$url] = $this->semesterToJSON($semester);
        }

        return $this->paginated($json, $total);
    }

    /**
     * Returns the semester week as string for a given string
     *
     * @get /semester/:timestamp/week
     */
    public function getSemesterWeek(int $timestamp)
    {
        $semester = \Semester::findByTimestamp($timestamp);
        if (!$semester) {
            return null;
        }
        $timestamp = strtotime('today', $timestamp);
        $week_begin_timestamp = strtotime('monday this week', $semester->vorles_beginn);
        $end_date = $semester->vorles_ende;

        $i = 0;
        $result = [
            'semester_name' => (string)$semester->name,
            'week_number' => sprintf(_('KW %u'), date('W', $timestamp)),
            'current_day' => strftime('%x', $timestamp)
        ];
        while ($week_begin_timestamp < $end_date) {
            $next_week_timestamp = strtotime('+1 week', $week_begin_timestamp);
            if ($week_begin_timestamp <= $timestamp && $timestamp < $next_week_timestamp) {
                $result['sem_week'] = sprintf(
                    _('%u. Vorlesungswoche (ab %s)'),
                    $i + 1,
                    strftime('%x', $week_begin_timestamp));
                break;
            }
            $i += 1;

            $week_begin_timestamp = $next_week_timestamp;
        }

        return $result;
    }

    /**
     * Returns a single semester.
     *
     * @get /semester/:semester_id
     */
    public function getSemester($id)
    {
        $semester = \Semester::find($id);
        if (!$semester) {
            $this->notFound();
        }

        $semester_json = $this->semesterToJSON($semester);
        $this->etag(md5(serialize($semester_json)));

        return $semester_json;
    }

    private function semesterToJSON($semester)
    {
        return [
            'id'             => $semester['semester_id'],
            'title'          => (string) $semester['name'],
            'token'          => (string) $semester['semester_token'],
            'description'    => (string) $semester['description'],
            'begin'          => (int) $semester['beginn'],
            'end'            => (int) $semester['ende'],
            'seminars_begin' => (int) $semester['vorles_beginn'],
            'seminars_end'   => (int) $semester['vorles_ende'],
            'visible'        => (int) $semester['visible'],
        ];
    }
}
