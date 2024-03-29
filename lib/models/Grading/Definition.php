<?php

namespace Grading;

/**
 * @license GPL2 or any later version
 *
 * @property int $id database column
 * @property string $course_id database column
 * @property string $item database column
 * @property string $name database column
 * @property string $tool database column
 * @property string $category database column
 * @property int $position database column
 * @property float $weight database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \SimpleORMapCollection|Instance[] $instances has_many Instance
 * @property \Course $course belongs_to \Course
 */
class Definition extends \SimpleORMap
{
    const CUSTOM_DEFINITIONS_CATEGORY = 'xyzzy';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'grading_definitions';

        $config['belongs_to']['course'] = [
            'class_name' => \Course::class,
            'foreign_key' => 'course_id',
        ];
        $config['has_many']['instances'] = [
            'class_name' => Instance::class,
            'assoc_foreign_key' => 'definition_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
        ];

        parent::configure($config);
    }

    public static function getCategoriesByCourse(\Course $course)
    {
        $query = 'SELECT category FROM grading_definitions
                  WHERE course_id = ?
                  GROUP BY category
                  ORDER BY category ASC';

        $stmt = \DBManager::get()->prepare($query);
        $stmt->execute([$course->id]);

        $categories = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $customIndex = array_search(self::CUSTOM_DEFINITIONS_CATEGORY, $categories);
        if (false !== $customIndex) {
            unset($categories[$customIndex]);
            array_unshift($categories, self::CUSTOM_DEFINITIONS_CATEGORY);
        }

        return $categories;
    }

    public static function findByCourse(\Course $course)
    {
        return Definition::findBySQL('course_id = ? ORDER BY position ASC, name ASC', [$course->id]);
    }
}
