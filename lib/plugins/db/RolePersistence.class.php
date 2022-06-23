<?php
/**
 * RolePersistence.class.php
 *
 * Funktionen für das Rollenmanagement
 *
 * @author      Dennis Reil <dennis.reil@offis.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @package     pluginengine
 * @subpackage  db
 * @copyright   2009 Stud.IP
 * @license     http://www.gnu.org/licenses/gpl.html GPL Licence 3
 */
class RolePersistence
{
    const ROLES_CACHE_KEY = 'roles';
    const USER_ROLES_CACHE_KEY = 'roles/user';
    const PLUGIN_ROLES_CACHE_KEY = 'roles/plugin';

    /**
     * Returns all available roles.
     *
     * @return array Roles
     */
    public static function getAllRoles(): array
    {
        // read cache
        $cache = StudipCacheFactory::getCache();

        // cache miss, retrieve from database
        $roles = $cache->read(self::ROLES_CACHE_KEY);
        if (!$roles) {
            $query = "SELECT `roleid`, `rolename`, `system` = 'y' AS `is_system`
                      FROM `roles`
                      ORDER BY `rolename`";
            $statement = DBManager::get()->query($query);
            $statement->setFetchMode(PDO::FETCH_ASSOC);

            $roles = [];
            foreach ($statement as $row) {
                $roles[$row['roleid']] = new Role($row['roleid'], $row['rolename'], $row['is_system']);
            }

            $cache->write(self::ROLES_CACHE_KEY, $roles);
        }

        return $roles;
    }

    public static function getRoleIdByName($name)
    {
        foreach (self::getAllRoles() as $id => $role) {
            if ($role->getRolename() === $name) {
                return $id;
            }
        }
        return false;
    }

    /**
     * Inserts the role into the database or does an update, if it's already there
     *
     * @param Role $role
     * @return int the role id
     */
    public static function saveRole($role)
    {
        $query = "INSERT INTO `roles` (`roleid`, `rolename`, `system`)
                  VALUES (?, ?, 'n')
                  ON DUPLICATE KEY UPDATE `rolename` = VALUES(`rolename`)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$role->getRoleId(), $role->getRolename()]);

        if ($role->getRoleid() === Role::UNKNOWN_ROLE_ID) {
            $role_id = DBManager::get()->lastInsertId();
            $role->setRoleid($role_id);

            $event = 'RoleDidCreate';
        } else {
            $event = 'RoleDidUpdate';
        }

        // sweep roles cache, see #getAllRoles
        self::expireRolesCache();

        NotificationCenter::postNotification(
            $event,
            $role->getRoleid(),
            $role->getRolename()
        );

        return $role->getRoleid();
    }

    /**
     * Delete role if not a permanent role. System roles cannot be deleted.
     *
     * @param Role $role
     */
    public static function deleteRole($role)
    {
        $id = $role->getRoleid();
        $name = $role->getRolename();

        $query = "SELECT `pluginid` FROM `roles_plugins` WHERE `roleid` = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $statement->setFetchMode(PDO::FETCH_COLUMN, 0);

        DBManager::get()->execute(
            "DELETE `roles`, `roles_user`, `roles_plugins`, `roles_studipperms`
             FROM `roles`
             LEFT JOIN `roles_user` USING (`roleid`)
             LEFT JOIN `roles_plugins` USING (`roleid`)
             LEFT JOIN `roles_studipperms` USING (`roleid`)
             WHERE `roleid` = ? AND `system` = 'n'",
            [$id]
        );

        // sweep roles cache
        self::expireRolesCache();

        foreach ($statement as $plugin_id) {
            unset(self::getPluginRolesCache()[$plugin_id]);
        }

        NotificationCenter::postNotification('RoleDidDelete', $id, $name);
    }

    /**
     * Saves a role assignment to the database
     *
     * @param User $user
     * @param Role $role
     * @param string $institut_id
     */
    public static function assignRole(User $user, $role, $institut_id = '')
    {
        // role is not in database
        // save it to the database first
        if ($role->getRoleid() !== Role::UNKNOWN_ROLE_ID) {
            $roleid = self::saveRole($role);
        } else {
            $roleid = $role->getRoleid();
        }

        $query = "REPLACE INTO `roles_user` (`roleid`, `userid`, `institut_id`)
                  VALUES (?, ?, ?)";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$roleid, $user->id, $institut_id]);

        unset(self::getUserRolesCache()[$user->id]);

        NotificationCenter::postNotification(
            'RoleAssignmentDidCreate',
            $roleid,
            $user->id,
            $institut_id
        );
    }

    /**
     * Gets all assigned roles from the database for a user
     *
     * @param int $userid
     * @param boolean $implicit
     * @return array
     */
    public static function getAssignedRoles($user_id, $implicit = false)
    {
        return array_intersect_key(
            self::getAllRoles(),
            self::loadUserRoles($user_id, $implicit)
        );
    }

    /**
     * Returns institutes for which the given user has the given role.
     * @param  string $user_id User id
     * @param  int    $role_id Role id
     * @return array of institute ids
     */
    public static function getAssignedRoleInstitutes($user_id, $role_id)
    {
        $roles = self::loadUserRoles($user_id);
        return $roles[$role_id] ?? [];
    }

    /**
     * Checks a role assignment for an user
     * optionally check for institute
     *
     * @param string $userid
     * @param string $assignedrole
     * @param string $institut_id
     * @return boolean
     */
    public static function isAssignedRole($userid, $assignedrole, $institut_id = '')
    {
        if (!$userid) {
            return false;
        }

        $faculty_id = $institut_id
                    ? Institute::find($institut_id)->fakultaets_id
                    : null;

        $role_id = self::getRoleIdByName($assignedrole);
        $user_roles = self::loadUserRoles($userid, true);

        return isset($user_roles[$role_id])
            && (
                 !$institut_id
                 || in_array($institut_id, $user_roles[$role_id])
                 || in_array($faculty_id, $user_roles[$role_id])
               );
    }

    private static function loadUserRoles($user_id, $implicit = false)
    {
        $cache = self::getUserRolesCache();

        if (!isset($cache[$user_id])) {
            $query = "SELECT `roleid`, `institut_id`, 1 AS explicit
                      FROM `roles_user`
                      WHERE `userid` = :user_id

                      UNION ALL

                      SELECT `roleid`, '' AS institut_id, 0 AS explicit
                      FROM `roles_studipperms`
                      WHERE `permname` = :perm";
            $statement = DBManager::get()->prepare($query);
            $statement->bindValue(':user_id', $user_id);
            $statement->bindValue(':perm', is_object($GLOBALS['perm']) ? $GLOBALS['perm']->get_perm($user_id) : 'nobody');
            $statement->execute();
            $statement->setFetchMode(PDO::FETCH_ASSOC);

            $roles = [];
            foreach ($statement as $row) {
                if (!isset($roles[$row['roleid']])) {
                    $roles[$row['roleid']] = [
                        'id'         => $row['roleid'],
                        'institutes' => [],
                        'explicit'   => (bool) $row['explicit'],
                    ];
                }
                if ($row['institut_id']) {
                    $roles[$row['roleid']]['institutes'][] = $row['institut_id'];
                }
            }

            $cache[$user_id] = $roles;
        }

        // Filter implicit roles away if necessary
        $roles = array_filter(
            $cache[$user_id],
            function ($role) use ($implicit) {
                return $implicit || $role['explicit'];
            }
        );

        return array_column($roles, 'institutes', 'id');
    }

    /**
     * Deletes a role assignment from the database
     *
     * @param User   $user
     * @param Role   $role
     * @param String $institut_id
     */
    public static function deleteRoleAssignment(User $user, $role, $institut_id = null)
    {
        $query = "DELETE FROM `roles_user`
                  WHERE `roleid` = ?
                    AND `userid` = ?
                    AND `institut_id` = IFNULL(?, `institut_id`)";
        DBManager::get()->execute(
            $query,
            [$role->getRoleid(), $user->id, $institut_id]
        );

        unset(self::getUserRolesCache()[$user->id]);

        NotificationCenter::postNotification(
            'RoleAssignmentDidDelete',
            $role->getRoleid(),
            $user->id,
            $institut_id
        );
    }

    /**
     * Get's all Role-Assignments for a certain user.
     * If no user is set, all role assignments are returned.
     *
     * @param User $user
     * @return array with roleids and the assigned userids
     * @deprecated seems to be unused (and was corrupt for some versions)
     */
    public static function getAllRoleAssignments($user = null)
    {
        $query = "SELECT `roleid`, `userid`
                  FROM `roles_user`
                  WHERE `userid` = IFNULL(?, `userid`)";
        return DBManager::get()->fetchPairs($query, [$user]);
    }

    /**
     * Enter description here...
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function assignPluginRoles($plugin_id, $role_ids)
    {
        $plugin_id = (int) $plugin_id;

        $query = "REPLACE INTO `roles_plugins` (`roleid`, `pluginid`)
                  VALUES (:role_id, :plugin_id)";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);

        foreach ($role_ids as $role_id) {
            $statement->bindValue(':role_id', $role_id);
            $statement->execute();
        }

        unset(self::getPluginRolesCache()[$plugin_id]);

        foreach ($role_ids as $role_id) {
            NotificationCenter::postNotification(
                'PluginRoleAssignmentDidCreate',
                $role_id,
                $plugin_id
            );
        }
    }

    /**
     * Removes the given roles' assignments from the given plugin.
     *
     * @param int $pluginid
     * @param array $roleids
     */
    public static function deleteAssignedPluginRoles($plugin_id, $role_ids)
    {
        $plugin_id = (int) $plugin_id;

        $query = "DELETE FROM `roles_plugins`
                  WHERE `pluginid` = :plugin_id
                    AND `roleid` = :role_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':plugin_id', $plugin_id);

        foreach ($role_ids as $role_id) {
            $statement->bindValue(':role_id', $role_id);
            $statement->execute();
        }

        unset(self::getPluginRolesCache()[$plugin_id]);

        foreach ($role_ids as $role_id) {
            NotificationCenter::postNotification(
                'PluginRoleAssignmentDidDelete',
                $role_id,
                $plugin_id
            );
        }
    }

    /**
     * Return all roles assigned to a plugin.
     *
     * @param int $pluginid
     * @return array
     */
    public static function getAssignedPluginRoles($plugin_id)
    {
        $plugin_id = (int) $plugin_id;

        // read plugin roles from cache
        $cache = self::getPluginRolesCache();

        // cache miss, retrieve roles from database
        if (!isset($cache[$plugin_id])) {
            $query = "SELECT `roleid` FROM `roles_plugins` WHERE `pluginid` = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$plugin_id]);
            $role_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

            // write to cache
            $cache[$plugin_id] = $role_ids;
        }

        $roles = self::getAllRoles();
        return array_filter(array_map(
            function ($role_id) use ($roles) {
                if (!isset($roles[$role_id])) {
                    return false;
                }
                return $roles[$role_id];
            },
            $cache[$plugin_id]
        ));
    }

    /**
     * Returns statistic values for each role:
     *
     * - number of explicitely assigned users
     * - number of implicitely assigned users
     * - number of assigned plugins
     *
     * @return array
     */
    public static function getStatistics()
    {
        // Get basic statistics
        $query = "SELECT r.`roleid`,
                         COUNT(DISTINCT ru.`userid`) AS explicit,
                         COUNT(DISTINCT rp.`pluginid`) AS plugins
                  FROM roles AS r
                  -- Explicit assignment
                  LEFT JOIN `roles_user` AS ru
                    ON r.`roleid` = ru.`roleid` AND ru.`userid` IN (SELECT `user_id` FROM `auth_user_md5`)
                  -- Plugins
                  LEFT JOIN `roles_plugins` AS rp
                    ON r.`roleid` = rp.`roleid` AND rp.`pluginid` IN (SELECT `pluginid` FROM `plugins`)
                  GROUP BY r.`roleid`";
        $result = DBManager::get()->fetchGrouped($query);

        // Fetch implicit assignments in a second query due to performance
        // reasons
        foreach (self::countImplicitUsers(array_keys($result)) as $id => $count) {
            $result[$id]['implicit'] = $count;
        }

        return $result;
    }

    /**
     * Counts the implicitely assigned users for a role.
     * @param  mixed $role_id Role id or array of role ids
     * @return mixed number of implictit for the role (if one role id is given)
     *               or associative array [role id => number of implicit users]
     *               when given a list of role ids
     */
    public static function countImplicitUsers($role_id)
    {
        // Ensure that the result array has an entry for every role id
        $result = array_fill_keys((array) $role_id, 0);

        $query = "SELECT rsp.`roleid`, COUNT(*) AS implicit
                  FROM `roles_studipperms` AS rsp
                  JOIN `auth_user_md5` AS a ON rsp.`permname` = a.`perms`
                  LEFT JOIN `roles_user` AS ru
                    ON a.`user_id` = ru.`userid` AND rsp.`roleid` = ru.`roleid`
                  WHERE rsp.`roleid` IN (?)
                    AND ru.`userid` IS NULL
                  GROUP BY rsp.`roleid`";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$role_id]);
        $statement->setFetchMode(PDO::FETCH_ASSOC);

        foreach ($statement as $row) {
            $result[$row['roleid']] = (int) $row['implicit'];
        }

        return is_array($role_id)
             ? $result
             : $result[$role_id];
    }

    // Cache operations
    private static $user_roles_cache = null;
    private static $plugin_roles_cache = null;

    private static function getUserRolesCache()
    {
        if (self::$user_roles_cache === null) {
            self::$user_roles_cache = new StudipCachedArray(self::USER_ROLES_CACHE_KEY);
        }
        return self::$user_roles_cache;
    }

    private static function getPluginRolesCache()
    {
        if (self::$plugin_roles_cache === null) {
            self::$plugin_roles_cache = new StudipCachedArray(self::PLUGIN_ROLES_CACHE_KEY);
        }
        return self::$plugin_roles_cache;
    }

    public static function expireRolesCache()
    {
        StudipCacheFactory::getCache()->expire(self::ROLES_CACHE_KEY);
    }

    public static function expireUserCache($user_id)
    {
        unset(self::getUserRolesCache()[$user_id]);
    }
}
