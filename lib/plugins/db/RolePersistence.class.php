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

    protected static $all_roles = null;

    /**
     * Returns all available roles.
     *
     * @param bool $grouped Return the roles grouped by system type or other
     * @return Role[]|array{system: Role[], other: Role[]}
     */
    public static function getAllRoles(bool $grouped = false): array
    {
        if (self::$all_roles === null) {
            // read cache
            $cache = StudipCacheFactory::getCache();

            // cache miss, retrieve from database
            self::$all_roles = $cache->read(self::ROLES_CACHE_KEY);
            if (!self::$all_roles) {
                $query = "SELECT `roleid`, `rolename`, `system` = 'y' AS `is_system`
                      FROM `roles`
                      ORDER BY `rolename`";
                $statement = DBManager::get()->query($query);
                $statement->setFetchMode(PDO::FETCH_ASSOC);

                self::$all_roles = [];
                foreach ($statement as $row) {
                    self::$all_roles[$row['roleid']] = new Role($row['roleid'], $row['rolename'], $row['is_system']);
                }

                $cache->write(self::ROLES_CACHE_KEY, self::$all_roles);
            }
        }

        if (!$grouped) {
            return self::$all_roles;
        }

        $groups = ['system' => [], 'other' => []];
        foreach (self::$all_roles as $id => $role) {
            $index = $role->getSystemtype() ? 'system' : 'other';
            $groups[$index][$id] = $role;
        }

        return $groups;
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
    public static function deleteRole($role): bool
    {
        $id = $role->getRoleid();
        $name = $role->getRolename();

        $query = "SELECT `pluginid` FROM `roles_plugins` WHERE `roleid` = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $statement->setFetchMode(PDO::FETCH_COLUMN, 0);

        $result = DBManager::get()->execute(
            "DELETE `roles`, `roles_user`, `roles_plugins`, `roles_studipperms`
             FROM `roles`
             LEFT JOIN `roles_user` USING (`roleid`)
             LEFT JOIN `roles_plugins` USING (`roleid`)
             LEFT JOIN `roles_studipperms` USING (`roleid`)
             WHERE `roleid` = ? AND `system` = 'n'",
            [$id]
        );

        if ($result === 0) {
            return false;
        }

        // sweep roles cache
        self::expireRolesCache();
        self::expireUserCache();

        foreach ($statement as $plugin_id) {
            self::expirePluginCache($plugin_id);
        }

        NotificationCenter::postNotification('RoleDidDelete', $id, $name);

        return true;
    }

    /**
     * Delete role by name if not a permanent role. System roles cannot be
     * deleted.
     *
     * @param string $role_name
     *
     * @return bool
     */
    public static function deleteRoleByName(string $role_name): bool
    {
        foreach (self::getAllRoles() as $role) {
            if ($role->getRolename() === $role_name) {
                return self::deleteRole($role);
            }
        }

        return false;
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
     * Assigns a role to a stud.ip permission. System roles cannot be assigned
     * to permissions.
     *
     * @param string $perm
     * @param Role   $role
     *
     * @return bool
     * @throws Exception
     */
    public static function assignRoleToPerm(string $perm, Role $role): bool
    {
        if ($role->getSystemtype()) {
            throw new Exception('Cannot assign system roles to permissions.');
        }

        if (!in_array($perm, ['user', 'autor', 'tutor', 'dozent', 'admin', 'root'])) {
            throw new Exception("Invalid permission {$perm}");
        }

        $query = "INSERT INTO `roles_studipperms` (`roleid`, `permname`)
                  VALUES (?, ?)";
        $result = DBManager::get()->execute($query, [$role->getRoleid(), $perm]);

        if ($result === 0) {
            return false;
        }

        User::findEachByPerms(
            function (User $user) {
                self::expireUserCache($user->id);
            },
            $perm
        );

        return true;
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
            $statement->bindValue(':perm', empty($GLOBALS['perm']) ? 'nobody' : $GLOBALS['perm']->get_perm($user_id));
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
     * Removes a role from a stud.ip permission. System roles cannot be removed
     * from permissions.
     *
     * @param string $perm
     * @param Role   $role
     *
     * @return bool
     * @throws Exception
     */
    public static function deleteRoleAssignmentFromPerm(string $perm, Role $role): bool
    {
        if ($role->getSystemtype()) {
            throw new Exception('Cannot remove system role assignment from permissions.');
        }

        if (!in_array($perm, ['user', 'autor', 'tutor', 'dozent', 'admin', 'root'])) {
            throw new Exception("Invalid permission {$perm}");
        }

        $query = "DELETE FROM `roles_studipperms`
                  WHERE `roleid` = ?
                    AND `permname` = ?";
        $result = DBManager::get()->execute($query, [$role->getRoleid(), $perm]);

        if ($result === 0) {
            return false;
        }

        User::findEachByPerms(
            function (User $user) {
                self::expireUserCache($user->id);
            },
            $perm
        );

        return true;
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

        self::expirePluginCache($plugin_id);

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

        self::expirePluginCache($plugin_id);

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
     * Returns all users that have a specific role - given by it's name.
     *
     * @param string $role_name   Name of the role
     * @param bool   $only_explicit Only select explicit assignments from table
     *                              `roles_user` if true, otherwise also select
     *                              by perm defined in table `roles_studipperms`
     *
     * @return User[]
     */
    public static function getUsersWithRoleByName(string $role_name, bool $only_explicit = true): array
    {
        $role_id = self::getRoleIdByName($role_name);
        if ($role_id === false) {
            throw new Exception("Unknown role name {$role_name}");
        }

        return self::getUsersWithRoleById($role_id, $only_explicit);
    }

    /**
     * Returns all users that have a specific role - given by it's id.
     *
     * @param int  $role_id       Id of the role
     * @param bool $only_explicit Only select explicit assignments from table
     *                            `roles_user` if true, otherwise also select
     *                            by perm defined in table `roles_studipperms`
     *
     * @return User[]
     */
    public static function getUsersWithRoleById(int $role_id, bool $only_explicit = true): array
    {
        $query = "SELECT `userid` AS `user_id`
                  FROM `roles_user`
                  WHERE `roleid` = :role_id";

        if (!$only_explicit) {
            $query = "SELECT DISTINCT `user_id`
                      FROM (
                          {$query}

                          UNION ALL

                          SELECT `user_id`
                          FROM `roles_studipperms` AS `rsp`
                          JOIN `auth_user_md5` AS `aum`
                            ON (`rsp`.`permname` = `aum`.`perms`)
                          WHERE `rsp`.`roleid` = :role_id
                      ) AS tmp";
        }

        $user_ids = DBManager::get()->fetchFirst($query, [':role_id' => $role_id]);

        return User::findMany($user_ids);
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

    private static function getUserRolesCache(): StudipCachedArray
    {
        if (self::$user_roles_cache === null) {
            self::$user_roles_cache = new StudipCachedArray(self::USER_ROLES_CACHE_KEY);
        }
        return self::$user_roles_cache;
    }

    private static function getPluginRolesCache(): StudipCachedArray
    {
        if (self::$plugin_roles_cache === null) {
            self::$plugin_roles_cache = new StudipCachedArray(self::PLUGIN_ROLES_CACHE_KEY);
        }
        return self::$plugin_roles_cache;
    }

    /**
     * Expires all cached roles.
     */
    public static function expireRolesCache()
    {
        self::$all_roles = null;
        StudipCacheFactory::getCache()->expire(self::ROLES_CACHE_KEY);
    }

    /**
     * Expires all cached user role assignments.
     *
     * @param string|null $user_id Optional user id to expire the cache for.
     *                             If none is given, the whole cache is cleared.
     */
    public static function expireUserCache($user_id = null)
    {
        if ($user_id === null) {
            self::getUserRolesCache()->expire();
        } else {
            unset(self::getUserRolesCache()[$user_id]);
        }
    }

    /**
     * Expires all cached plugin role assignments.
     *
     * @param string|int|null $plugin_id Optional plugin id to expire the cache
     *                                   for. If none is given, the whole cache
     *                                   is cleared.
     */
    public static function expirePluginCache($plugin_id = null)
    {
        if ($plugin_id === null) {
            self::getPluginRolesCache()->expire();
        } else {
            unset(self::getPluginRolesCache()[$plugin_id]);
        }
    }

    /**
     * Expires all caches
     */
    public static function expireCaches(): void
    {
        self::expireRolesCache();
        self::expireUserCache();
        self::expirePluginCache();
    }
}
