<?php

namespace Courseware;

use User;

/**
 * Courseware's tasks.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.1
 *
 * @property int $id database column
 * @property string $seminar_id database column
 * @property string $lecturer_id database column
 * @property int $target_id database column
 * @property int $task_template_id database column
 * @property int $solver_may_add_blocks database column
 * @property string $title database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \SimpleORMapCollection|Task[] $tasks has_many Task
 * @property \User $lecturer belongs_to \User
 * @property \Course $course belongs_to \Course
 */
class TaskGroup extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_task_groups';

        $config['belongs_to']['lecturer'] = [
            'class_name' => User::class,
            'foreign_key' => 'lecturer_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name' => \Course::class,
            'foreign_key' => 'seminar_id',
        ];

        $config['has_many']['tasks'] = [
            'class_name' => Task::class,
            'assoc_foreign_key' => 'task_group_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY mkdate',
        ];

        parent::configure($config);
    }

    public function getSolvers(): iterable
    {
        $solvers = $this->tasks->pluck('solver');

        return $solvers;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $task_groups = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_task_groups WHERE lecturer_id = ?',
            [$storage->user_id]
        );
        if ($task_groups) {
            $storage->addTabularData(_('Courseware Aufgaben'), 'cw_task_groups', $task_groups);
        }
        
    }
}
