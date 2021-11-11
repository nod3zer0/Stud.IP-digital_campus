<?php
# Lifter001: DONE
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/*
seminar_main.php - Die Eingangs- und Uebersichtsseite fuer ein Seminar
Copyright (C) 2000 Stefan Suchi <suchi@gmx.de>, Ralf Stockmann <rstockm@gwdg.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/


require '../lib/bootstrap.php';

ob_start();
page_open(["sess" => "Seminar_Session", "auth" => "Seminar_Default_Auth", "perm" => "Seminar_Perm", "user" => "Seminar_User"]);
$auth->login_if(Request::get('again') && ($auth->auth["uid"] == "nobody"));

if (Request::option('auswahl')) {
    Request::set('cid', Request::option('auswahl'));
}

include ('lib/seminar_open.php'); // initialise Stud.IP-Session

// -- here you have to put initialisations for the current page

$course_id = Context::getId();

if (!$course_id && Request::get('cid')) {
    $archive_id = Request::get('cid');
    $archived = ArchivedCourse::find($archive_id);
    if ($archived) {
        header('Location: ' . URLHelper::getURL('dispatch.php/search/archive', [
            'criteria' => $archived->name,
        ]));
        die;
    }
}

if (!$course_id) {
    throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
}

//set visitdate for course, when coming from my_courses
if (Request::get('auswahl')) {
   object_set_visit($course_id, 0);
}


// gibt es eine Anweisung zur Umleitung?
$redirect_to = Request::get('redirect_to');
if ($redirect_to) {
    if (!is_internal_url($redirect_to)) {
        throw new Exception('Invalid redirection');
    }

    header('Location: '.URLHelper::getURL($redirect_to, ['cid' => $course_id]));
    die;
}

// der Nutzer zum ersten
//Reiter der Veranstaltung weiter geleitet.
if (Navigation::hasItem("/course")) {
    foreach (Navigation::getItem("/course")->getSubNavigation() as $navigation) {
        header('Location: ' . URLHelper::getURL($navigation->getURL()));
        die;
    }
}

