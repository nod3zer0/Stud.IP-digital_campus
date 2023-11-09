<?php
/**
 * WidgetUser.php
 * model class for table widget_user
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @property int $id database column
 * @property int $pluginid database column
 * @property int $position database column
 * @property string $range_id database column
 * @property int $col database column
 * @property User $range belongs_to User
 */

class WidgetUser extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'widget_user';

        $config['belongs_to']['range'] = [
            'class_name'  => User::class,
            'foreign_key' => 'range_id'
        ];

        parent::configure($config);
    }

    /**
     * Store the default layout for the specified user.
     *
     * @param string $user_id
     */
    public static function setInitialWidgets($user_id): void
    {
        if (self::countBySQL('range_id = ?', [$user_id]) === 0) {
            $stmt = DBManager::get()->prepare(
                'INSERT INTO widget_user (pluginid, position, range_id, col)
                 SELECT pluginid, position, :user_id, col FROM widget_default WHERE perm = :perm'
            );
            $stmt->execute([
                'user_id' => $user_id,
                'perm' => $GLOBALS['perm']->get_perm($user_id)
            ]);
        }
    }

    /**
     * Return the list of widgets (by column) shown to this user.
     *
     * @param string $user_id
     *
     * @return array array of columns with widget ids
     */
    public static function getWidgets($user_id): array
    {
        $widgets = self::findBySQL('range_id = ? ORDER BY position', [$user_id]);
        $result = [];

        foreach ($widgets as $widget) {
            $result[$widget->col][] = $widget->pluginid;
        }

        if (empty($result)) {
            $result = WidgetDefault::getWidgets($GLOBALS['perm']->get_perm($user_id));
        }

        return $result;
    }

    /**
     * Return whether the user has a certain widget enabled.
     *
     * @param string $user_id
     * @param int    $plugin_id
     *
     * @return bool
     */
    public static function hasWidget($user_id, $plugin_id): bool
    {
        $widgets = self::getWidgets($user_id);

        return in_array($plugin_id, array_merge(...$widgets));
    }

    /**
     * Add a widget for the given user (left column).
     *
     * @param string $user_id
     * @param string $plugin_id
     *
     * @return WidgetUser inserted WidgetUser instance
     */
    public static function addWidget($user_id, $plugin_id): WidgetUser
    {
        self::setInitialWidgets($user_id);

        $stmt = DBManager::get()->prepare('SELECT MAX(position) + 1 FROM widget_user WHERE range_id = ?');
        $stmt->execute([$user_id]);
        $position = $stmt->fetchColumn() ?: 0;

        return self::create([
            'pluginid' => $plugin_id,
            'position' => $position,
            'range_id' => $user_id
        ]);
    }

    /**
     * Remove a widget for the given user (if enabled).
     *
     * @param string $user_id
     * @param string $plugin_id
     *
     * @return int number of removed widgets
     */
    public static function removeWidget($user_id, $plugin_id): int
    {
        self::setInitialWidgets($user_id);

        return self::deleteBySQL('pluginid = ? AND range_id = ?', [$plugin_id, $user_id]);
    }
}
