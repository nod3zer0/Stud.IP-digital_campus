<?php

namespace eTask;

use JSONArrayObject;

/**
 * eTask conforming test task relation.
 *
 * @property array $id alias for pk
 * @property int $test_id database column
 * @property int $task_id database column
 * @property int $position database column
 * @property float|null $points database column
 * @property \JSONArrayObject $options database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property Test $test belongs_to Test
 * @property Task $task belongs_to Task
 */
class TestTask extends \SimpleORMap
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_test_tasks';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['test'] = [
            'class_name' => $config['relationTypes']['Test'],
            'foreign_key' => 'test_id'];

        $config['belongs_to']['task'] = [
            'class_name' => $config['relationTypes']['Task'],
            'foreign_key' => 'task_id'];

        $config['serialized_fields']['options'] = JSONArrayObject::class;

        parent::configure($config);
    }
}
