<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: TODO
/**
 * index.php - Startseite von Stud.IP (anhaengig vom Status)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Stefan Suchi <suchi@gmx.de>
 * @author      Ralf Stockmann <rstockm@gwdg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

require '../lib/bootstrap.php';

page_open(['sess' => 'Seminar_Session', 'auth' => 'Seminar_Default_Auth', 'perm' => 'Seminar_Perm', 'user' => 'Seminar_User']);

$auth->login_if($user->id === 'nobody');
include 'lib/seminar_open.php'; // initialise Stud.IP-Session

// if new start page is in use, redirect there (if logged in)
if ($auth->is_authenticated() && $user->id != 'nobody') {
    header('Location: ' . URLHelper::getURL('dispatch.php/start'));
}
