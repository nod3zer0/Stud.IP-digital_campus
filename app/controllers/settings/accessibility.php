<?php
/**
 * Settings_AccessibilityController - Administration of all user accessibility related
 * settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Michaela Brückner <brueckner@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.2
 */
require_once 'settings.php';

class Settings_AccessibilityController extends Settings_SettingsController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Barrierefreiheit'));
        Navigation::activateItem('/profile/settings/accessibility');
        SkipLinks::addIndex(_('Barrierefreiheitseinstellungen anpassen'), 'layout_content', 100);
    }

    public function index_action()
    {

    }

    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $this->config->store('USER_HIGH_CONTRAST', Request::bool('enable_high_contrast'));
        $this->config->store('SKIPLINKS_ENABLE', Request::bool('skiplinks_enable'));

        PageLayout::postSuccess(_('Ihre Einstellungen wurden gespeichert.'));
        $this->redirect('settings/accessibility');
    }

}
