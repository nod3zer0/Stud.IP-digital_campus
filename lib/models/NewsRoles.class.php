<?php

/**
 * NewsRoles.class.php - model class for the news roles
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Sebastian Biller <s.biller@tu-braunschweig.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       5.1
 *
 * @property string news_id database column
 * @property int roleid database column
 */

class NewsRoles extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'news_roles';

        $config['belongs_to']['news_ranges'] = [
            'class_name'  => StudipNews::class,
            'foreign_key' => 'news_id',
        ];

        parent::configure($config);
    }

    public static function checkUserAccess($news_id, $user_id = null)
    {
        $user_id = $user_id ?: (isset($GLOBALS['user']) ? $GLOBALS['user']->id : null);
        $news_roles = self::getRoles($news_id);

        if (!$news_roles) {
            return true;
        }

        if (!$user_id) {
            return false;
        }

        $user_roles = RolePersistence::getAssignedRoles($user_id, true);

        foreach ($news_roles as $news_role) {
            foreach ($user_roles as $user_role) {
                if ($news_role->getRoleid() === $user_role->getRoleid()) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getRoles($news_id)
    {
        $news_roles = self::findBynews_id($news_id);
        $news_role_ids = [];
        foreach ($news_roles as $news_role) {
            $news_role_ids[] = $news_role['roleid'];
        }

        $only_system_roles = Config::get()->NEWS_ONLY_SYSTEM_ROLES;
        $roles = RolePersistence::getAllRoles();
        $re = [];
        foreach ($news_role_ids as $role_id) {
            if (isset($roles[$role_id])) {
                if ($only_system_roles && !$roles[$role_id]->getSystemtype()) {
                    continue;
                }
                $re[$role_id] = $roles[$role_id];
            }
        }
        return $re;
    }

    public static function getAvailableRoles($news_id = null)
    {
        $news_role_ids = [];
        if ($news_id) {
            $news_roles = self::findBynews_id($news_id);
            foreach ($news_roles as $news_role) {
                $news_role_ids[] = $news_role['roleid'];
            }
        }

        $only_system_roles = Config::get()->NEWS_ONLY_SYSTEM_ROLES;
        $roles = RolePersistence::getAllRoles();
        $rolesStats = RolePersistence::getStatistics();
        $re = [];
        foreach ($roles as $key => $role) {
            if (!in_array($key, $news_role_ids)) {
                if ($only_system_roles && !$role->getSystemtype()) {
                    continue;
                }
                if ($rolesStats[$role->getRoleid()]['explicit'] + $rolesStats[$role->getRoleid()]['implicit'] == 0) {
                    continue;
                }
                $re[$key] = $role;
            }
        }

        return $re;
    }

    public static function update($news_id, $new_roles)
    {
        self::deleteBynews_id($news_id);

        if ($new_roles) {
            foreach ($new_roles as $new_role) {
                $NewsRoles = new self();
                $NewsRoles->news_id = $news_id;
                $NewsRoles->roleid = $new_role;
                $NewsRoles->store();
            }
        }
    }

    public static function load($new_roles)
    {
        $roles = RolePersistence::getAllRoles();

        return array_filter(array_map(
            function ($role_id) use ($roles) {
                if (!isset($roles[$role_id])) {
                    return false;
                }
                return [$role_id, $roles[$role_id]];
            },
            $new_roles
        ));
    }
}
