<?php

namespace eTask;

use JSONArrayObject;

/**
 * eTask conforming assignment-range relation definition.
 *
 * @property int $id database column
 * @property int $assignment_id database column
 * @property string $range_type database column
 * @property string $range_id database column
 * @property \JSONArrayObject $options database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property Assignment $assignment belongs_to Assignment
 */
class AssignmentRange extends \SimpleORMap
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignment_ranges';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['serialized_fields']['options'] = JSONArrayObject::class;

        parent::configure($config);
    }
}
