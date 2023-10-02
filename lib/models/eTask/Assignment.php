<?php

namespace eTask;

use JSONArrayObject;

/**
 * eTask conforming assignment definition.
 *
 * @property int $id database column
 * @property int $test_id database column
 * @property string|null $range_type database column
 * @property string|null $range_id database column
 * @property string $type database column
 * @property int|null $start database column
 * @property int|null $end database column
 * @property int $active database column
 * @property \JSONArrayObject $options database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property \SimpleORMapCollection|Attempt[] $attempts has_many Attempt
 * @property \SimpleORMapCollection|AssignmentRange[] $ranges has_many AssignmentRange
 * @property \SimpleORMapCollection|Response[] $responses has_many Response
 * @property Test $test belongs_to Test
 */
class Assignment extends \SimpleORMap
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignments';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['test'] = [
            'class_name' => $config['relationTypes']['Test'],
            'foreign_key' => 'test_id'
        ];

        $config['has_many']['attempts'] = [
            'class_name' => $config['relationTypes']['Attempt'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['ranges'] = [
            'class_name' => $config['relationTypes']['AssignmentRange'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['has_many']['responses'] = [
            'class_name' => $config['relationTypes']['Response'],
            'assoc_foreign_key' => 'assignment_id',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        $config['serialized_fields']['options'] = JSONArrayObject::class;

        parent::configure($config);
    }
}
