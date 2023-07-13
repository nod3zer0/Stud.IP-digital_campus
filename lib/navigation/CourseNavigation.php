<?php
# Lifter010: TODO
/*
 * CourseNavigation.php - navigation for course / institute area
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

class CourseNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        global $user, $perm;

        // check if logged in
        if (is_object($user) && $user->id != 'nobody') {
            $coursetext = _('Veranstaltungen');
            $courseinfo = _('Meine Veranstaltungen & Einrichtungen');
            $courselink = 'dispatch.php/my_courses';
        } else {
            $coursetext = _('Freie');
            $courseinfo = _('Freie Veranstaltungen');
            $courselink = 'dispatch.php/public_courses';
        }

        parent::__construct($coursetext, $courselink);

        if (is_object($user)) {
            $this->setImage(Icon::create('seminar', 'navigation', ["title" => $courseinfo]));
        }
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();

        $context = Context::get();
        if (!$context) {
            return;
        }

        $admin_plugin_ids = [];
        $core_admin = PluginManager::getInstance()->getPlugin('CoreAdmin');
        if ($core_admin) {
            $admin_plugin_ids[] = $core_admin->getPluginId();
        }
        $core_studygroup_admin = PluginManager::getInstance()->getPlugin('CoreStudygroupAdmin');
        if ($core_studygroup_admin) {
            $admin_plugin_ids[] = $core_studygroup_admin->getPluginId();
        }
        $tools = $context->tools->getArrayCopy();
        usort($tools, function ($a, $b) use ($admin_plugin_ids) {
            if (in_array($a['plugin_id'], $admin_plugin_ids)) {
                return -1;
            }
            if (in_array($b['plugin_id'], $admin_plugin_ids)) {
                return 1;
            }
            return $a['position'] - $b['position'];
        });
        foreach ($tools as $tool) {
            if (Context::isInstitute() || Seminar_Perm::get()->have_studip_perm($tool->getVisibilityPermission(), $context->id)) {
                $studip_module = $tool->getStudipModule();
                if ($studip_module instanceof StudipModule) {
                    $tool_nav = $studip_module->getTabNavigation($context->id) ?: [];
                    foreach ($tool_nav as $nav_name => $navigation) {
                        if ($nav_name && is_a($navigation, "Navigation")) {
                            if ($tool->metadata['displayname']) {
                                $navigation->setTitle($tool->getDisplayname());
                            }
                            $this->addSubNavigation($nav_name, $navigation);
                        }
                    }
                }
            }
        }
    }

}
