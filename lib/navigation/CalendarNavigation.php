<?php
# Lifter010: TODO
/*
 * CalendarNavigation.php - navigation for calendar
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class CalendarNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        $title = _('Kalender');
        $main_url = URLHelper::getURL('dispatch.php/calendar/calendar');
        if (!$GLOBALS['perm']->have_perm('admin') && Config::get()->SCHEDULE_ENABLE) {
            $title = _('Stundenplan');
            $main_url = URLHelper::getURL('dispatch.php/calendar/schedule');
        }
        parent::__construct($title, $main_url);

        $this->setImage(Icon::create('schedule', 'navigation', ['title' => $title]));
    }

    /**
     * Initialize the sub-navigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        global $perm, $atime;

        parent::initSubNavigation();

        if (!$perm->have_perm('admin') && Config::get()->SCHEDULE_ENABLE) {
            $navigation = new Navigation(_('Stundenplan'), 'dispatch.php/calendar/schedule');
            $this->addSubNavigation('schedule', $navigation);
        }

        if (Config::get()->CALENDAR_ENABLE) {
            $navigation = new Navigation(_('Kalender'), 'dispatch.php/calendar/calendar');
            $this->addSubNavigation('calendar', $navigation);
        }
    }
}
