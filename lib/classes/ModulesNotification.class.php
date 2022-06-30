<?php
# Lifter002: DONE - no html output in this file
# Lifter007: TODO
# Lifter003: TEST
# Lifter010: DONE - no html output in this file
/**
* ModulesNotification.class.php
*
* check for modules (global and local for institutes and Veranstaltungen), read and write
*
*
* @author       Peter Thienel <thienel@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
* @access       public
* @modulegroup      core
* @package      studip_core
*/

// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// Modules.class.php
// Checks fuer Module (global und lokal fuer Veranstaltungen und Einrichtungen), Schreib-/Lesezugriff
// Copyright (C) 2003 Cornelis Kater <ckater@gwdg.de>, Suchi & Berg GmbH <info@data-quest.de>
// +---------------------------------------------------------------------------+
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or any later version.
// +---------------------------------------------------------------------------+
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
// +---------------------------------------------------------------------------+

class ModulesNotification
{

    public $registered_notification_modules = [];
    public $subject;

    function __construct ()
    {
        foreach (MyRealmModel::getDefaultModules() as $id => $module) {
            if (!is_object($module)) continue;
            $this->registered_notification_modules[$id] = [
                'icon' => $module->getMetadata()['icon'],
                'name' => $module->getMetadata()['displayname'] ?: $module->getPluginName()
            ];
            if ($module instanceof CoreOverview) {
                $this->registered_notification_modules[$id]['name'] = _("Ankündigungen");
                $this->registered_notification_modules[$id]['icon'] = Icon::create('news');
            }
            if (!is_object($this->registered_notification_modules[$id]['icon'])) {
                $icon = $module->getPluginURL() . '/' . $this->registered_notification_modules[$id]['icon'];
                $this->registered_notification_modules[$id]['icon'] = Icon::create($icon);
            }
        }
        $this->registered_notification_modules[-1] =
            [
                'name' => _("Umfragen und Tests"),
                'icon' => Icon::create('vote')
            ];
        $this->registered_notification_modules[0] =
            [
                'name' => _("Grunddaten der Veranstaltung"),
                'icon' => Icon::create('seminar')
            ];

        $this->subject = _("Stud.IP Benachrichtigung");
    }



    function getAllNotifications ($user_id = NULL)
    {

        if (is_null($user_id)) {
            $user_id = $GLOBALS['user']->id;
        }

        $my_sem = [];
        $query = "SELECT s.Seminar_id, s.Name, s.chdate, s.start_time, IFNULL(visitdate, :threshold) AS visitdate "
               . "FROM seminar_user_notifications su "
               . "LEFT JOIN seminare s USING (Seminar_id) "
               . "LEFT JOIN object_user_visits ouv ON (ouv.object_id = su.Seminar_id AND ouv.user_id = :user_id AND ouv.plugin_id = 0) "
               . "WHERE su.user_id = :user_id";

        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', $user_id);
        $statement->bindValue(':threshold', object_get_visit_threshold());
        $statement->execute();
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $seminar_id = $row['Seminar_id'];
            $tools = ToolActivation::findbyRange_id($seminar_id);
            $notification = CourseMemberNotification::find([$user_id, $seminar_id]);
            $my_sem[$seminar_id] = [
                    'name'       => $row['Name'],
                    'chdate'     => $row['chdate'],
                    'start_time' => $row['start_time'],
                    'tools'    => new SimpleCollection($tools),
                    'visitdate'  => $row['visitdate'],
                    'notification'=> $notification ? $notification->notification_data->getArrayCopy() : []
            ];
        }
        $visit_data = get_objects_visits(array_keys($my_sem), 'sem', null, $user_id, array_keys($this->registered_notification_modules));
        $news = [];
        foreach ($my_sem as $seminar_id => $s_data) {
            if (!count($s_data['notification'])) {
                continue;
            }
            $navigation = MyRealmModel::getAdditionalNavigations($seminar_id, $s_data, null, $user_id, $visit_data[$seminar_id]);
            $n_data = [];
            foreach ($this->registered_notification_modules as $id => $m_data) {
                if (in_array($id, $s_data['notification'])
                    && isset($navigation[$id])
                    && $navigation[$id]->getImage()
                    && $navigation[$id]->getImage()->getRole() === Icon::ROLE_ATTENTION
                ) {
                        $data = $this->getPluginText($navigation[$id], $seminar_id, $id);
                        if ($data) {
                            $n_data[] = $data;
                        }
                    }
            }
            if (count($n_data)) {
                $news[$s_data['name']] = $n_data;
            }
        }
        if (count($news)) {
            $auth_plugin = User::find($user_id)->auth_plugin;
            if (!is_a('StudipAuth' . ucfirst($auth_plugin), 'StudipAuthSSO', true)) {
                $auth_plugin = null;
            }
            $template = $GLOBALS['template_factory']->open('mail/notification_html');
            $template->set_attribute('lang', getUserLanguagePath($user_id));
            $template->set_attribute('news', $news);
            $template->set_attribute('sso', $auth_plugin);

            $template_text = $GLOBALS['template_factory']->open('mail/notification_text');
            $template_text->set_attribute('news', $news);
            $template_text->set_attribute('sso', $auth_plugin);
            return ['text' => $template_text->render(), 'html' => $template->render()];
        }
    }

    function getPluginText($nav, $seminar_id, $id)
    {
        $base_url = URLHelper::setBaseURL('');
        URLHelper::setBaseURl($base_url);
        if ($nav instanceof Navigation && $nav->isVisible(true)) {
                $url = 'seminar_main.php?again=yes&auswahl=' . $seminar_id . '&redirect_to=' . strtr($nav->getURL(), '?', '&');
                $icon = $nav->getImage();
                $text = $nav->getTitle();
                if (!$text) {
                    $text = $this->registered_notification_modules[$id]['name'];
                }
                $text .= ' - ' .  $nav->getLinkAttributes()['title'];
                return compact('text', 'url', 'icon', 'seminar_id');
            }
    }
}
