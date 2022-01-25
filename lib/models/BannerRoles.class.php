<?php

/**
 * BannerRoles.class.php - model class for the banner roles
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
 * @property string ad_id database column
 * @property int roleid database column
 */

class BannerRoles extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'banner_roles';

        $config['belongs_to']['banner_ads'] = [
            'class_name'  => Banner::class,
            'foreign_key' => 'ad_id',
        ];

        parent::configure($config);
    }

    public static function checkUserAccess($ad_id, $user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        $banner_roles = self::getRoles($ad_id);
        $user_roles = RolePersistence::getAssignedRoles($user_id, true);

        if (!$banner_roles) {
            return true;
        }

        foreach ($banner_roles as $banner_role) {
            foreach ($user_roles as $user_role) {
                if ($banner_role->getRoleid() === $user_role->getRoleid()) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getRoles($ad_id)
    {
        $banner_roles = self::findByad_id($ad_id);
        $banner_role_ids = [];
        foreach ($banner_roles as $banner_role) {
            $banner_role_ids[] = $banner_role['roleid'];
        }

        $only_system_roles = Config::get()->BANNER_ONLY_SYSTEM_ROLES;
        $roles = RolePersistence::getAllRoles();
        $re = [];
        foreach ($banner_role_ids as $role_id) {
            if (isset($roles[$role_id])) {
                if ($only_system_roles && !$roles[$role_id]->getSystemtype()) {
                    continue;
                }
                $re[$role_id] = $roles[$role_id];
            }
        }
        return $re;
    }

    public static function getAvailableRoles($ad_id = null)
    {
        $banner_role_ids = [];
        if ($ad_id) {
            $banner_roles = self::findByad_id($ad_id);
            foreach ($banner_roles as $banner_role) {
                $banner_role_ids[] = $banner_role['roleid'];
            }
        }

        $only_system_roles = Config::get()->BANNER_ONLY_SYSTEM_ROLES;
        $roles = RolePersistence::getAllRoles();
        $rolesStats = RolePersistence::getStatistics();
        $re = [];
        foreach ($roles as $key => $role) {
            if (!in_array($key, $banner_role_ids)) {
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

    public static function update($ad_id, $new_roles)
    {
        self::deleteByAd_id($ad_id);

        if ($new_roles) {
            foreach ($new_roles as $new_role) {
                $BannerRoles = new self();
                $BannerRoles->ad_id = $ad_id;
                $BannerRoles->roleid = $new_role;
                $BannerRoles->store();
            }
        }
    }
}
