<?php
/**
 * Settings_DeputiesController - Administration of all user deputy
 * related settings
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

require_once 'settings.php';

class Settings_DeputiesController extends Settings_SettingsController
{
    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array $args Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.MyStudIPDeputies');
        PageLayout::setTitle(_('Standardvertretung'));
        Navigation::activateItem('/profile/settings/deputies');

        $this->edit_about_enabled = Config::get()->DEPUTIES_EDIT_ABOUT_ENABLE;
    }

    /**
     * Displays the deputy information of a user.
     */
    public function index_action()
    {
        if (Request::submitted('add_deputy') && $deputy_id = Request::option('deputy_id')) {
            $this->check_ticket();

            if (Deputy::isDeputy($deputy_id, $this->user->user_id)) {
                PageLayout::postError(sprintf(
                    _('%s ist bereits als Vertretung eingetragen.'),
                    htmlReady(get_fullname($deputy_id))
                ));
            } else if ($deputy_id == $this->user->user_id) {
                PageLayout::postError(_('Sie können sich nicht als Ihre eigene Vertretung eintragen!'));
            } else if (Deputy::addDeputy($deputy_id, $this->user->user_id)) {
                PageLayout::postSuccess(sprintf(
                    _('%s wurde als Vertretung eingetragen.'),
                    htmlReady(get_fullname($deputy_id))
                ));
            } else {
                PageLayout::postError(_('Fehler beim Eintragen der Vertretung!'));
            }
            $this->redirect('settings/deputies');
            return;
        }

        $deputies = Deputy::findDeputies($this->user->user_id);
        $deputy_user_ids = $deputies->pluck('user_id');
        $exclude_users = [$this->user->user_id];
        if (is_array($deputies)) {
            $exclude_users = array_merge($exclude_users, array_map(function ($d) {
                return $d['user_id'];
            }, $deputy_user_ids));
        }

        $this->deputies = $deputies;

        $this->search = new PermissionSearch(
            'user',
            _('Vor-, Nach- oder Benutzername'),
            'user_id',
            [
                'permission' => Deputy::getValidPerms(),
                'exclude_user' => $exclude_users
            ]
        );

        $actions = new ActionsWidget();
        $mp = MultiPersonSearch::get('settings_add_deputy')
            ->setLinkText(_('Neue Standardvertretung festlegen'))
            ->setDefaultSelectedUser($deputy_user_ids)
            ->setLinkIconPath('')
            ->setTitle(_('Neue Standardvertretung festlegen'))
            ->setExecuteURL(URLHelper::getLink('dispatch.php/settings/deputies/add_member'))
            ->setSearchObject($this->search)
            ->setNavigationItem('/links/settings/deputies')
            ->render();
        $element = LinkElement::fromHTML($mp, Icon::create('add'));
        $actions->addElement($element);
        Sidebar::Get()->addWidget($actions);
    }


    public function add_member_action()
    {
        CSRFProtection::verifyRequest();

        $mp = MultiPersonSearch::load('settings_add_deputy');
        $msg = [
            'error' => [],
            'success' => [],
        ];
        foreach ($mp->getAddedUsers() as $_user_id) {
            if (Deputy::isDeputy($_user_id, $this->user->user_id)) {
                $msg['error'][] = sprintf(
                    _('%s ist bereits als Vertretung eingetragen.'),
                    htmlReady(get_fullname($_user_id))
                );
            } else if ($_user_id == $this->user->user_id) {
                $msg['error'][] = _('Sie können sich nicht als Ihre eigene Vertretung eintragen!');
            } else if (!Deputy::addDeputy($_user_id, $this->user->user_id)) {
                $msg['error'][] = _('Fehler beim Eintragen der Vertretung!');
            } else {
                $msg['success'][] = sprintf(
                    _('%s wurde als Vertretung eingetragen.'),
                    htmlReady(get_fullname($_user_id))
                );
            }
        }
        // only show an error messagebox once.
        if (!empty($msg['error'])) {
            PageLayout::postError(
                _('Die gewünschte Operation konnte nicht ausgeführt werden.'),
                htmlReady($msg['error'])
            );
        }
        if (!empty($msg['success'])) {
            PageLayout::postSuccess(
                _('Die gewünschten Personen wurden als Ihre Vertretung eingetragen!'),
                htmlReady($msg['success'])
            );
        }

        $this->redirect('settings/deputies');
    }

    /**
     * Delete deputy
     * @param Deputy $deputy
     */
    public function delete_action(Deputy $deputy)
    {
        CSRFProtection::verifyUnsafeRequest();
        if ($deputy->delete()) {
            PageLayout::postSuccess(_('Die Vertretung wurde entfernt.'));
        }
        $this->redirect('settings/deputies');
    }

    /**
     * Stores the deputy settings of a user.
     */
    public function store_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        if ($this->edit_about_enabled) {
            $deputies = Deputy::findDeputies($this->user->user_id);
            $changes = Request::intArray('edit_about');
            $success = true;
            $changed = 0;
            foreach ($deputies as $deputy) {
                $state = (int)$changes[$deputy->user_id];
                if ($state !== (int)$deputy->edit_about) {
                    $deputy->edit_about = $state;
                    if ($deputy->store()) {
                        $success = true;
                        $changed++;
                    }
                }
            }
            if ($success && $changed > 0) {
                PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
            } else if ($changed > 0) {
                PageLayout::postError(_('Fehler beim Speichern der Einstellungen.'));
            }
        }

        $this->redirect('settings/deputies');
    }
}
