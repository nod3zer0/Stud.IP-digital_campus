<?php
/**
 * WidgetDefault.php
 * model class for table widget_default
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @property array $id alias for pk
 * @property int $pluginid database column
 * @property int $col database column
 * @property int $position database column
 * @property string $perm database column
 */

class WidgetDefault extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'widget_default';

        parent::configure($config);
    }

    public static function getWidgets($perm): array
    {
        $result = [];
        $widgets = self::findBySQL('perm = ? ORDER BY position', [$perm]);

        foreach ($widgets as $widget) {
            $result[$widget->col][] = $widget->pluginid;
        }

        return $result;
    }
}
