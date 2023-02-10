<?php

namespace Courseware;

use User;

/**
 * Courseware's units.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.3
 * 
 * @property int                            $id                     database column
 * @property string                         $range_id               database column
 * @property string                         $range_type             database column
 * @property int                            $structural_element_id  database column
 * @property string                         $content_type           database column
 * @property int                            $public                 database column
 * @property string                         $creator_id             database column
 * @property int                            $release_date           database column
 * @property int                            $withdraw_date          database column
 * @property int                            $mkdate                 database column
 * @property int                            $chdate                 database column
 * @property \User                          $creator                belongs_to User
 * @property \Courseware\StructuralElement  $structural_element     belongs_to Courseware\StructuralElement
 * 
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */

class Unit extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_units';

        $config['has_one']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
            'on_delete' => 'delete',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => \Course::class,
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'seminar_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'user_id',
        ];
        $config['belongs_to']['creator'] = [
            'class_name' => User::class,
            'foreign_key' => 'creator_id',
        ];

        parent::configure($config);
    }

    public static function findCoursesUnits(\Course $course): array
    {
        return self::findBySQL('range_id = ? AND range_type = ?', [$course->id, 'course']);
    }

    public static function findUsersUnits(\User $user): array
    {
        return self::findBySQL('range_id = ? AND range_type = ?', [$user->id, 'user']);
    }

    public function canRead(\User $user): bool
    {
        return $this->structural_element->canRead($user);
    }

    public function canEdit(\User $user): bool
    {
        return $this->structural_element->canEdit($user);;
    }

    public function copy(\User $user, string $rangeId, string $rangeType, array $modified = null): Unit
    {
        $sourceUnitElement = $this->structural_element;

        $newElement = $sourceUnitElement->copyToRange($user, $rangeId, $rangeType);

        if ($modified !== null) {
            $newElement->title = $modified['title'] ?? $newElement->title;
            $newElement->payload['color'] = $modified['color'] ?? 'studip-blue';
            $newElement->payload['description'] = $modified['description'] ?? $newElement->payload['description'];
            $newElement->store();
        }

        $newUnit = \Courseware\Unit::build([
            'range_id' => $rangeId,
            'range_type' => $rangeType,
            'structural_element_id' => $newElement->id,
            'content_type' => 'courseware',
            'creator_id' => $user->id,
            'public' => '',
            'release_date' => null,
            'withdraw_date' => null,
        ]);
        
        $newUnit->store();

        return $newUnit;
    }
}
