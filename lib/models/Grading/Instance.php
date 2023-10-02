<?php

namespace Grading;

/**
 * @license GPL2 or any later version
 *
 * @property array $id alias for pk
 * @property int $definition_id database column
 * @property string $user_id database column
 * @property float $rawgrade database column
 * @property string|null $feedback database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \User $user belongs_to \User
 * @property Definition $definition belongs_to Definition
 */
class Instance extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'grading_instances';

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'user_id',
        ];
        $config['belongs_to']['definition'] = [
            'class_name' => Definition::class,
            'foreign_key' => 'definition_id',
        ];

        parent::configure($config);
    }

    public static function findByCourse(\Course $course)
    {
        $definitionIds = Definition::findAndMapBySQL(
            function ($def) {
                return $def->id;
            },
            'course_id = ?',
            [$course->id]
        );

        if (!count($definitionIds)) {
            return [];
        }

        return self::findBySql('definition_id IN (?)', [$definitionIds]);
    }

    public static function findByCourseAndUser(\Course $course, \User $user)
    {
        $definitionIds = Definition::findAndMapBySQL(
            function ($def) {
                return $def->id;
            },
            'course_id = ?',
            [$course->id]
        );

        if (!count($definitionIds)) {
            return [];
        }

        return self::findBySql('definition_id IN (?) AND user_id = ?', [$definitionIds, $user->id]);
    }
}
