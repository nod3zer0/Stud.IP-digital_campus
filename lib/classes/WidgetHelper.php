<?php
/**
 * WidgetHelper.php - utility functions for Widget-Parameter Handling
 * @deprecated since Stud.IP 5.5
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Nadine Werner <nadine.werner@uni-osnabrueck.de>
 * @author   André Klaßen <klassen@elan-ev.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @package  index
 * @since    3.1
 */

class WidgetHelper
{
    /**
     * array of submitted widget parameter values
     */
    private static $params = [];

    /**
     * array of submitted widget parameter values
     */
    private static $activeWidget;

    /**
     * Saves the widget data of a user
     */
    private static $userWidgets = [];

    /**
     * Set the last active Widget
     * @param string $activeWidget
     */
    public static function setActiveWidget($activeWidget)
    {
        self::$activeWidget = $activeWidget;
    }

    /**
     * Returns the position in the two column layout on the Startpage
     * If no position is stored in UserConfig, the widget will be displayed on the right side.
     *
     * @param string $pluginid
     *
     * @return the position as array matrix
     */
    public static function getWidgetPosition($pluginid)
    {
        $query = "SELECT position FROM widget_user where id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$pluginid]);
        $pos = $statement->fetchColumn();

        return $pos;
    }

    /**
     * storeNewPositions - stores new Widget positions for a given user
     *
     * @param array $lanes array with column as index and ids array as value
     *
     * @return void
     */
    public static function storeNewPositions(array $lanes): void
    {
        // Query not displayed widgets to sort them to the bottom of a lane
        $query = "SELECT `col`, `id`
                  FROM `widget_user`
                  WHERE `range_id` = ?
                    AND `id` NOT IN (?)
                  ORDER BY `col`, `position`";
        $undisplayed = DBManager::get()->fetchGrouped($query, [
            User::findCurrent()->id,
            array_merge(...$lanes)
        ], function ($row) {
            return array_column($row, 'id');
        });

        // Set new positions
        $query = "UPDATE `widget_user`
                  SET `col` = :column,
                      `position` = :position
                  WHERE `id` = :id
                    AND `range_id` = :user_id";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':user_id', User::findCurrent()->id);

        foreach ([0, 1] as $column) {
            $statement->bindValue(':column', $column);

            $ids = array_merge(
                $lanes[$column] ?? [],
                $undisplayed[$column] ?? []
            );

            $position = 0;
            foreach ($ids as $id) {
                $statement->bindValue(':position', $position++);
                $statement->bindValue(':id', $id);
                $statement->execute();
            }
        }
    }

    /**
     * addInitialPositons - adds the global widget default settings to an user setting
     *
     * @param string $col
     * @param array $ids of widgets
     * @param string $range_id
     *
     * @return void
     */
    public static function addInitialPositions($col, $ids, $range_id)
    {
        if(is_array($ids)) {
             foreach ($ids as $pos => $id) {
                  $pos = intVal($pos);
                  $query = "REPLACE INTO widget_user (`pluginid`, `position`, `range_id`) VALUES (?,?,?);";
                  $statement = DBManager::get()->prepare($query);
                  $statement->execute([$id, $pos, $range_id]);
             }
        }
    }

    /**
     * storeInitialPositions - stores the global widget default for a given perm
     *
     * @param string $col
     * @param array $ids of widgets
     * @param string $perm
     *
     * @return boolean success
     */
    public static function storeInitialPositions($col, $ids, $perm)
    {
        $stmt = DBManager::get()->prepare('DELETE FROM widget_default WHERE `perm` = ? AND `col` = ?;');
        $stmt->execute([$perm, $col]);

        if (is_array($ids)) {
            foreach ($ids as $id => $pos) {
                if ($id) {
                    $pos = intVal($pos);
                    $stmt = DBManager::get()->prepare("REPLACE INTO widget_default (`pluginid`,`col`, `position`, `perm`) VALUES (?,?,?,?);");
                    $stmt->execute([$id, $col, $pos, $perm]);
                }
            }

            return true;
        }

        return false;
    }

    public static function getInitialPositions($perm)
    {
        return DBManager::get()->fetchGroupedPairs("SELECT col, pluginid, position FROM widget_default "
                . "WHERE perm = ? "
                . "ORDER BY col ASC, position ASC", [$perm]);
    }

    /**
     * Sets the current setting of a user as the default for a usergroup
     *
     * @param string $range_id The range id of the user that defines the setting
     * @param string $group The usergroup
     */
    public static function setAsInitialPositions($range_id, $group)
    {
        DBManager::get()->execute("DELETE FROM widget_default WHERE `perm` = ?", [$group]);
        DBManager::get()->execute('INSERT INTO widget_default (SELECT pluginid, col, position, ? as perm  FROM widget_user WHERE range_id = ?)', [$group, $range_id]);
    }

    /**
     * setInitialPositions - copies the default to the logged on user
     */
    public static function setInitialPositions()
    {
        $query = "INSERT INTO widget_user (pluginid, position, range_id, col)
                  SELECT pluginid, position, :user_id, col
                  AS perm
                  FROM widget_default
                  WHERE perm = :perm

                  UNION

                  -- Dummy entry to allow no widgets
                  SELECT -1, 0, :user_id, 2";
        DBManager::get()->execute($query, [
            ':user_id' => $GLOBALS['user']->id,
            ':perm'    => $GLOBALS['perm']->get_perm(),
        ]);
    }

    /**
     * getUserWidgets - retrieves the widget settings for a given user
     *
     * @param string $id
     *
     * @return array $widgets
     */
    public static function getUserWidgets($id, $col = 0)
    {
        $plugin_manager = PluginManager::getInstance();
        $query = "SELECT * FROM widget_user WHERE range_id=? AND col = ? ORDER BY position";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id, $col]);
        $widgets = [];
        while ($db_widget = $statement->fetch(PDO::FETCH_ASSOC)) {
            if(!is_null($plugin_manager->getPluginById($db_widget['pluginid']))){
                $widget = clone $plugin_manager->getPluginById($db_widget['pluginid']);
                $widget->widget_id = $db_widget['id'];
                $widgets[$db_widget['position']] = $widget;
            }
        }
        return $widgets;
    }

    /**
     * Returns whether a user has any defined widgets.
     * @param  string  $user_id User id
     * @return boolean
     */
    public static function hasUserWidgets($user_id)
    {
        $query = "SELECT 1 FROM `widget_user` WHERE `range_id` = ?";
        return (bool) DBManager::get()->fetchColumn($query, [$user_id]);
    }

    /**
     * addWidgetUserConfig - creates user_config entry for widget newly added by a user
     *
     * @param string $id - user_id
     * @param string $pluginName
     * @param array $confArray
     *
     * @return void
     */
    public static function addWidgetUserConfig($id, $pluginName, $confArray )
    {
        UserConfig::get($id)->store($pluginName, $confArray );
    }


    /**
     * getWidgetUserConfig - retrieves user_config entry for widget newly added by a user
     *
     * @param string $id user_id
     * @param string $pluginName
     *
     * @return object UserConfig
     */
    public static function getWidgetUserConfig($id, $pluginName)
    {
        return UserConfig::get($id)->getValue($pluginName);

    }

    /**
     * removeWidget - removes a widget for a user
     *
     * @param string $id - widget_id
     * @param string $pluginName
     * @param string $range_id e.g. user_id
     *
     * @return bool success
     */
    public static function removeWidget($id, $pluginName, $range_id)
    {
        UserConfig::get($range_id)->delete($pluginName);

        $query = "DELETE FROM widget_user WHERE id = ? AND range_id = ?";
        $statement = DBManager::get()->prepare($query);

        return $statement->execute([$id, $range_id]);
    }

    /**
     * addWidget - adds a widget for a given user
     *
     * @param string $id - widget_id
     * @param string $range_id e.g. user_id
     *
     * @return bool|int false on error, id of inserted widget otherwise
     */
    public static function addWidget($id, $range_id)
    {
        $db = DBManager::get();
        $statement = $db->prepare('SELECT MAX(position) + 1 FROM widget_user WHERE range_id = :range_id');
        $statement->bindValue(':range_id', $range_id);
        $statement->execute();
        $position = $statement->fetchColumn() ?: 0;

        $statement = $db->prepare('INSERT INTO widget_user (pluginid, position, range_id) VALUES (:id, :position, :range_id)');
        $statement->bindValue(':id', $id);
        $statement->bindValue(':position', $position);
        $statement->bindValue(':range_id', $range_id);
        $result = $statement->execute();

        return $result ? $db->lastInsertId() : false;
    }

    /**
     * getWidgetName - retrieves the name of a given widget
     *
     * @param string $id - widget_id
     *
     * @return string widget_name
     */
    public static function getWidgetName($id)
    {
        $query = "SELECT `pluginid` FROM `widget_user` WHERE `id`=?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$id]);
        $pid = $statement->fetch(PDO::FETCH_ASSOC);

        $plugin_manager = PluginManager::getInstance();
        $plugin_info = $plugin_manager->getPluginById($pid['pluginid']);
        return $plugin_info ? $plugin_info->getPluginName() : false;

    }


    /**
     * getWidget - retrieves an instance of a given widget / portal plugin
     *
     * @param string $pluginid
     *
     * @return object widget
     */
    public static function getWidget($pluginid)
    {
        return PluginManager::getInstance()->getPluginById($pluginid);
    }

    /**
     * getAvailableWidgets - fetches all widgets that are not already in use.
     *
     * @param string $user_id the user to check
     *
     * @return array All available widgets.
     */
    public static function getAvailableWidgets($user_id = null)
    {
        $all_widgets = PluginEngine::getPlugins('PortalPlugin');

        $used_widgets = is_null($user_id)
                ? []
                : DBManager::get()->fetchFirst("SELECT `pluginid` FROM `widget_user` WHERE `range_id`=? ORDER BY `pluginid`", [$user_id]);

        $available = [];
        foreach ($all_widgets as $widget) {
            if (!in_array($widget->getPluginId(), $used_widgets)) {
                $available[$widget->getPluginId()] = $widget;
            }
        }
        return $available;
    }

    /**
     * hasWidget - returns whether has a certain widget activated
     *
     * @param string $user_id Id of the user
     * @param mixed  $widget  Id or name of the widget (you may omit the
     *                        'Widget' in the name)
     * @return bool indicating whether the widget is activated
     */
    public static function hasWidget($user_id, $widget)
    {
        if (!isset(self::$userWidgets[$user_id])) {
            $statement = DBManager::get()->prepare("
                SELECT *
                FROM widget_user
                WHERE range_id = :user_id
            ");
            $statement->execute(['user_id' => $user_id]);
            self::$userWidgets[$user_id] = $statement->fetchAll(PDO::FETCH_ASSOC);
        }

        if (!ctype_digit($widget)) {
            $plugin = PluginManager::getInstance()->getPlugin($widget) ?: PluginManager::getInstance()->getPlugin($widget . "Widget");
            if ($plugin) {
                $widget = $plugin->getPluginId();
            } else {
                return false;
            }
        }

        foreach (self::$userWidgets[$user_id] as $widget_user) {
            if ($widget_user['pluginid'] == $widget) {
                return true;
            }
        }
        return false;
    }
}
