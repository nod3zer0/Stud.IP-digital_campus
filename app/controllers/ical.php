<?php
/*
 * ical.php - iCalendar export controller
 *
 * Copyright (C) 2011 - Peter Thienel <thienel@data-quest.de>, Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */
class iCalController extends StudipController
{

    function before_filter(&$action, &$args) {
        // allow only "word" characters in arguments
        $this->validate_args($args);
    }

    /**
     * Handles the download the calendar data as iCalendar for the
     * user identified by $key.
     *
     *
     * @global Seminar_User $user
     * @global Seminar_Perm $perm
     * @param string $key
     * @param string $type type of export
     */
    function index_action($key = '')
    {
        if (mb_strlen($key)) {
            $user_id = IcalExport::getUserIdByKey($key);
        } else {
            $username = $_SERVER['PHP_AUTH_USER'];
            $password = $_SERVER['PHP_AUTH_PW'];
            if (isset($username) && isset($password)) {
                $result = StudipAuthAbstract::CheckAuthentication($username, $password);
            }
            if (isset($result) && $result['uid'] !== false) {
                $user_id = $result['uid'];
            } else {
               $this->response->add_header('WWW-Authenticate', 'Basic realm="Stud.IP Login"');
               $this->set_status(401);
               $this->render_text('authentication failed');
               return;
            }
        }

        if ($user_id) {
            $GLOBALS['user'] = new Seminar_User($user_id);
            $GLOBALS['perm'] = new Seminar_Perm();

            $end = DateTime::createFromFormat('U', '2114377200');
            $start = new DateTime();
            $start->modify('-4 week');
            $ical_export = new ICalendarExport();
            $ical = $ical_export->exportCalendarDates($user_id, $start, $end)
                  . $ical_export->exportCourseDates($user_id, $start, $end)
                  . $ical_export->exportCourseExDates($user_id, $start, $end);
            $content = $ical_export->writeHeader() . $ical . $ical_export->writeFooter();
            if (mb_stripos($_SERVER['HTTP_USER_AGENT'], 'google-calendar') !== false) {
                $content = str_replace(['CLASS:PRIVATE','CLASS:CONFIDENTIAL'], 'CLASS:PUBLIC', $content);
            }
            $this->response->add_header('Content-Type', 'text/calendar;charset=utf-8');
            $this->response->add_header('Content-Disposition', 'attachment; filename="studip.ics"');
            $this->response->add_header('Content-Transfer-Encoding', 'binary');
            $this->response->add_header('Pragma', 'public');
            $this->response->add_header('Cache-Control', 'private');
            $this->response->add_header('Content-Length', strlen($content));
            $this->render_text($content);
        } else {
            // delayed response to prevent brute force attacks ???

            $this->set_status(400);
            $this->render_nothing();
        }
    }

}
