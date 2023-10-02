<?php
/**
 * LogAction
 * model class for table log_actions
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 *
 * @property string $id alias column for action_id
 * @property string $action_id database column
 * @property string $name database column
 * @property string|null $description database column
 * @property string|null $info_template database column
 * @property int $active database column
 * @property int $expires database column
 * @property string|null $filename database column
 * @property string|null $class database column
 * @property string|null $type database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property SimpleORMapCollection|LogEvent[] $events has_many LogEvent
 */
class LogAction extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'log_actions';

        $config['has_many']['events'] = [
            'class_name' => LogEvent::class,
            'on_delete'  => 'delete',
        ];

        parent::configure($config);
    }

    /**
     * Returns whether this action is active or not.
     *
     * @return boolean TRUE if action is active.
     */
    public function isActive()
    {
        return (bool) $this->active;
    }

    /**
     * Returns an associative array of all actions with at least one event.
     * The array contains the action_id and the description. It is ordered by
     * the first part of the actions name and the description.
     *
     * @param bool $grouped Return array grouped by group name
     * @return array Assoc array of actions.
     */
    public static function getUsed($grouped = false)
    {
        $sql = "SELECT action_id, description, SUBSTRING_INDEX(name, '_', 1) AS log_group
                FROM log_actions WHERE EXISTS
                (SELECT * FROM log_events WHERE log_events.action_id = log_actions.action_id)
                ORDER BY log_group, description";
        $result = DBManager::get()->fetchAll($sql);

        if (!$grouped) {
            return $result;
        }

        $actions = [];
        foreach ($result as $action) {
            extract($action);

            if (!isset($actions[$log_group])) {
                $actions[$log_group] = [];
            }
            $actions[$log_group][$action_id] = $description;
        }
        return $actions;
    }
}
