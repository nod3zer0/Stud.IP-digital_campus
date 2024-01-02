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

$auth->login_if(Request::get('again') && ($auth->auth['uid'] == 'nobody'));

// if desired, switch to high contrast stylesheet and store when user logs in
if (Request::submitted('user_config_submitted')) {
    CSRFProtection::verifyUnsafeRequest();
    if (Request::submitted('unset_contrast')) {
        $_SESSION['contrast'] = 0;
    }
    if (Request::submitted('set_contrast')) {
        $_SESSION['contrast'] = 1;
    }

// evaluate language clicks
// has to be done before seminar_open to get switching back to german (no init of i18n at all))
    foreach (array_keys($GLOBALS['INSTALLED_LANGUAGES']) as $language_key) {
        if (Request::submitted('set_language_' . $language_key)) {
            $_SESSION['forced_language'] = $language_key;
            $_SESSION['_language'] = $language_key;
        }
    }
}
// store user-specific language preference
if ($auth->is_authenticated() && $user->id != 'nobody') {
    // store last language click
    if (!empty($_SESSION['forced_language'])) {
        $query = "UPDATE user_info SET preferred_language = ? WHERE user_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$_SESSION['forced_language'], $user->id]);

        $_SESSION['_language'] = $_SESSION['forced_language'];
    }
    $_SESSION['forced_language'] = null;
}

// -- wir sind jetzt definitiv in keinem Seminar, also... --
closeObject();

include 'lib/seminar_open.php'; // initialise Stud.IP-Session
$auth->login_if($user->id === 'nobody');

// if new start page is in use, redirect there (if logged in)
if ($auth->is_authenticated() && $user->id != 'nobody') {
    header('Location: ' . URLHelper::getURL('dispatch.php/start'));
}
