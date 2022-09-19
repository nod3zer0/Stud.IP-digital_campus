<?php

namespace eTask;

/**
 * eTask conforming test definition.
 *
 * @property int id database column
 * @property string title database column
 * @property string description database column
 * @property string user_id database column
 * @property string mkdate database column
 * @property string chdate database column
 * @property string options database column
 * @property SimpleORMapCollection tasks additional field etask\Task
 * @property SimpleORMapCollection testtasks has_many etask\TestTask
 * @property User owner belongs_to User
 * @property SimpleORMapCollection assignments has_many etask\Assignment
 * @property JSONArrayobject options serialized database column
 */
class Test extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_tests';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['has_and_belongs_to_many']['tasks'] = [
            'class_name' => $config['relationTypes']['Task'],
            'assoc_foreign_key' => 'id',
            'thru_table' => 'etask_test_tasks',
            'thru_key' => 'test_id',
            'thru_assoc_key' => 'task_id',
            'order_by' => 'ORDER BY position'
        ];

        $config['has_many']['testtasks'] = [
            'class_name' => $config['relationTypes']['TestTask'],
            'assoc_foreign_key' => 'test_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['belongs_to']['owner'] = [
            'class_name'  => '\\User',
            'foreign_key' => 'user_id'
        ];

        $config['has_many']['assignments'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'assoc_foreign_key' => 'test_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['options'] = 'JSONArrayObject';

        parent::configure($config);
    }

    /**
     * Retrieve the tasks associated to this test.
     * @deprecated - use $this->tasks instead.
     *
     * @return SimpleORMapCollection the associated tasks
     */
    public function getTasks()
    {
        $this->initRelation('tasks');
        return $this->relations['tasks'];
    }

    /**
     * Count all the tasks related to this test.
     *
     * @return int the number of related tasks
     */
    public function countTasks()
    {
        return count($this->testtasks);
    }

    /**
     * Convenience method to associate a task to this test.
     * Creates a TestTask object and returns it.
     *
     * @param eTask\Task task the task to associate
     *
     * @return eTask\TestTask the created TestTask object
     */
    public function addTask($task)
    {
        $class = static::config('relationTypes')['TestTask'];

        $testTask = $class::create(
            [
                'test_id' => $this->id,
                'task_id' => $task->id,
                'position' => $this->countTasks() + 1,
                'options' => []
            ]
        );

        $this->resetRelation('tasks');
        $this->resetRelation('testtasks');

        return $testTask;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('eTask Tests'), 'etask_tests', $field_data);
            }
        }

        $field_data = \DBManager::get()->fetchAll("SELECT * FROM etask_test_tags WHERE user_id =?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('eTask Tests Tags'), 'etask_test_tags', $field_data);
        }
    }
}
