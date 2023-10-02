<?php

namespace eTask;

use JSONArrayObject;
use User;

/**
 * eTask conforming task definition.
 *
 * @property int $id database column
 * @property string $type database column
 * @property string $title database column
 * @property string $description database column
 * @property \JSONArrayObject $task database column
 * @property string $user_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \JSONArrayObject $options database column
 * @property \SimpleORMapCollection|TestTask[] $test_tasks has_many TestTask
 * @property \SimpleORMapCollection|Response[] $responses has_many Response
 * @property \User $owner belongs_to \User
 * @property \SimpleORMapCollection|Test[] $tests has_and_belongs_to_many Test
 */
class Task extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['owner'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id'
        ];

        $config['has_and_belongs_to_many']['tests'] = [
            'class_name' => $config['relationTypes']['Test'],
            'assoc_foreign_key' => 'id',
            'thru_table' => 'etask_test_tasks',
            'thru_key' => 'task_id',
            'thru_assoc_key' => 'test_id'
        ];

        $config['has_many']['test_tasks'] = [
            'class_name' => $config['relationTypes']['TestTask'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['responses'] = [
            'class_name' => $config['relationTypes']['Response'],
            'assoc_foreign_key' => 'task_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['task'] = JSONArrayObject::class;
        $config['serialized_fields']['options'] = JSONArrayObject::class;

        parent::configure($config);
    }

    /**
     * Retrieve the tests associated to this task.
     * @deprecated - use $this->tests instead.
     *
     * @return SimpleORMapCollection the associated tests
     */
    public function getTests()
    {
        $this->initRelation('tests');
        return $this->relations['tests'];
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
                $storage->addTabularData(_('eTask Aufgaben'), 'etask_tasks', $field_data);
            }
        }

        $field_data = \DBManager::get()->fetchAll("SELECT * FROM etask_task_tags WHERE user_id =?", [$storage->user_id]);
        if ($field_data) {
            $storage->addTabularData(_('eTask Aufgaben Tags'), 'etask_task_tags', $field_data);
        }
    }
}
