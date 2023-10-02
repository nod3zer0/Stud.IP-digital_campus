<?php
// +---------------------------------------------------------------------------+
// This file is part of Stud.IP
// CronjobSchedule.class.php
//
// Copyright (C) 2013 Jan-Hendrik Willms <tleilax+studip@gmail.com>
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

/**
 * CronjobTask - Model for the database table "cronjobs_tasks"
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 *
 * @property string $id alias column for task_id
 * @property string $task_id database column
 * @property string $filename database column
 * @property string $class database column
 * @property int $active database column
 * @property int $execution_count database column
 * @property int $assigned_count database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property SimpleORMapCollection|CronjobSchedule[] $schedules has_many CronjobSchedule
 * @property-read mixed $description additional field
 * @property-read mixed $name additional field
 * @property-read mixed $parameters additional field
 */
class CronjobTask extends SimpleORMap
{
    /**
     * Configures the model.
     *
     * @param Array $config Optional configuration passed from derived class
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cronjobs_tasks';
        $config['has_many']['schedules'] = [
            'class_name' => CronjobSchedule::class,
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];

        $config['additional_fields'] = [
            'description' => [
                'get' => function (CronjobTask $task): string {
                    if ($task->valid) {
                        return $task->class::getDescription();
                    }
                    return _('Unbekannt');
                },
            ],
            'name' => [
                'get' => function (CronjobTask $task): string {
                    if ($task->valid) {
                        return $task->class::getName();
                    }
                    $result = $task->filename;
                    if (strpos($result, 'public/plugins_packages') !== false) {
                        $result = preg_replace('/.*public\/plugins_packages\/(.+)(_Cronjob)?(\.class)?\.php$/', '$1', $result);
                    } else {
                        $result = preg_replace('/(_Cronjob)?(\.class)?\.php$/', '', basename($result));
                    }
                    $result .= ' (' . _('fehlerhaft') . ')';
                    return $result;
                },
            ],
            'parameters' => [
                'get' => function (CronjobTask $task): array {
                    if ($task->valid) {
                        return $task->class::getParameters();
                    }
                    return [];
                },
            ],
        ];

        $config['registered_callbacks']['after_initialize'][] = 'loadClass';

        parent::configure($config);
    }

    public $valid = false;

    /**
     * Tries to load the underlying php class. This also determines the valid
     * state of the task. If the class does not exists, the task is marked
     * as not valid.
     */
    protected function loadClass()
    {
        $this->valid = false;

        if (empty($this->class)) {
            return;
        }

        $filename = $GLOBALS['STUDIP_BASE_PATH'] . '/' . $this->filename;
        if (!file_exists($filename)) {
            return;
        }

        require_once $filename;

        $this->valid = class_exists($this->class);
    }

    /**
     * Returns whether the task is defined in the core system or via a plugin.
     *
     * @return bool True if task is defined in core system
     */
    public function isCore()
    {
        return mb_strpos($this->filename, 'plugins_packages') === false;
    }

    /**
     * Executes the task.
     *
     * @param String $last_result Result of last executions
     * @param Array  $parameters  Parameters to pass to the task
     */
    public function engage($last_result, $parameters = [])
    {
        if ($this->valid) {
            $parameters = array_merge(
                $this->extractDefaultParameters(),
                $parameters
            );

            $task = new $this->class;

            $task->setUp();
            $result = $task->execute($last_result, $parameters);
            $task->tearDown();
        } else {
            $result = $last_result;
        }

        return $result;
    }

// Convenience methods to ease the usage

    /**
     * Schedules this task for a single execution at the provided time.
     *
     * @param int    $timestamp  When the task should be executed
     * @param String $priority   Priority of the execution (low, normal, high),
     *                           defaults to normal
     * @param Array  $parameters Optional parameters passed to the task
     * @return CronjobSchedule The generated schedule object.
     */
    public function scheduleOnce($timestamp, $priority = CronjobSchedule::PRIORITY_NORMAL,
                                 $parameters = [])
    {
        return CronjobScheduler::getInstance()->scheduleOnce(
            $this->id,
            $timestamp,
            $priority,
            $parameters
        );
    }

    /**
     * Schedules this task for periodic execution with the provided schedule.
     *
     * @param mixed  $minute      Minute part of the schedule:
     *                            - null for "every minute" a.k.a. "don't care"
     *                            - x < 0 for "every x minutes"
     *                            - x >= 0 for "only at minute x"
     * @param mixed  $hour        Hour part of the schedule:
     *                            - null for "every hour" a.k.a. "don't care"
     *                            - x < 0 for "every x hours"
     *                            - x >= 0 for "only at hour x"
     * @param mixed  $day         Day part of the schedule:
     *                            - null for "every day" a.k.a. "don't care"
     *                            - x < 0 for "every x days"
     *                            - x > 0 for "only at day x"
     * @param mixed  $month       Month part of the schedule:
     *                            - null for "every month" a.k.a. "don't care"
     *                            - x < 0 for "every x months"
     *                            - x > 0 for "only at month x"
     * @param mixed  $day_of_week Day of week part of the schedule:
     *                            - null for "every day" a.k.a. "don't care"
     *                            - 1 >= x >= 7 for "exactly at day of week x"
     *                              (x starts with monday at 1 and ends with
     *                               sunday at 7)
     * @param String $priority   Priority of the execution (low, normal, high),
     *                           defaults to normal
     * @param Array  $parameters Optional parameters passed to the task
     * @return CronjobSchedule The generated schedule object.
     */
    public function schedulePeriodic($minute = null, $hour = null,
                                     $day = null, $month = null, $day_of_week = null,
                                     $priority = CronjobSchedule::PRIORITY_NORMAL,
                                     $parameters = [])
    {
        return CronjobScheduler::getInstance()->schedulePeriodic(
            $this->id,
            $minute,
            $hour,
            $day,
            $month,
            $day_of_week,
            $priority,
            $parameters
        );
    }

    /**
     * Extracts the default parameter values from the associated task.
     *
     * @return array
     */
    public function extractDefaultParameters()
    {
        $parameters = call_user_func("{$this->class}::getParameters");
        return array_map(function ($parameter) {
            // return $parameter['default'] ?? null;
            return isset($parameter['default']) ? $parameter['default'] : null;
        }, $parameters);
    }
}
