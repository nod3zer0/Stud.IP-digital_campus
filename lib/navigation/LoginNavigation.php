<?php
# Lifter010: TODO
/*
 * LoginNavigation.php - navigation for login page
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

class LoginNavigation extends Navigation
{
    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $standard_login_active = false;
        foreach (StudipAuthAbstract::getInstance() as $auth_plugin) {
            if ($auth_plugin->show_login && !$standard_login_active) {
                $navigation = new Navigation(_('Login'), '');
                $navigation->setDescription($auth_plugin->login_description ?: _('für registrierte Nutzende'));
                $navigation->setLinkAttributes([
                    'id' => 'toggle-login'
                ]);
                $navigation->setURL('#toggle-login');
                $this->addSubNavigation('standard_login', $navigation);
                $standard_login_active = true;
            }
            if ($auth_plugin instanceof StudipAuthSSO && isset($auth_plugin->login_description)) {
                $navigation = new Navigation($auth_plugin->plugin_fullname . ' ' . _('Login'), '?sso=' . $auth_plugin->plugin_name);
                $navigation->setDescription($auth_plugin->login_description);
                $this->addSubNavigation('login_' . $auth_plugin->plugin_name, $navigation);
            }
        }

        if (Config::get()->ENABLE_SELF_REGISTRATION) {
            $navigation = new Navigation(_('Registrieren'), 'dispatch.php/registration');
            $navigation->setDescription(_('um das System erstmalig zu nutzen'));
            $this->addSubNavigation('registration', $navigation);
        }

        if (Config::get()->ENABLE_FREE_ACCESS) {
            $navigation = new Navigation(_('Freier Zugang'), 'dispatch.php/public_courses');
            $navigation->setDescription(_('ohne Registrierung'));
            $this->addSubNavigation('browse', $navigation);
        }

        $navigation = new Navigation(_('Hilfe'), format_help_url('Basis.Allgemeines'));
        $navigation->setDescription(_('zu Bedienung und Funktionsumfang'));
        $this->addSubNavigation('help', $navigation);
    }
}
