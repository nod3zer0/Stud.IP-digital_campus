<?php

namespace Courseware;

use User;

/**
 * Courseware's clipboards.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.4
 * 
 * @property int                            $id                     database column
 * @property string                         $user_id                database column
 * @property string                         $name                   database column
 * @property string                         $description            database column
 * @property int                            $block_id               database column
 * @property int                            $container_id           database column
 * @property int                            $structural_element_id  database column
 * @property string                         $object_type            database column
 * @property string                         $object_kind            database column
 * @property string                         $backup                 database column
 * @property int                            $mkdate                 database column
 * @property int                            $chdate                 database column
 * @property \User                          $user                   belongs_to User
 * @property Block $block belongs_to Block
 * @property Container $container belongs_to Container
 * @property StructuralElement $structural_element belongs_to StructuralElement
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */

class Clipboard extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_clipboards';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['block'] = [
            'class_name' => Block::class,
            'foreign_key' => 'block_id',
        ];

        $config['belongs_to']['container'] = [
            'class_name' => Container::class,
            'foreign_key' => 'container_id',
        ];
    
        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        parent::configure($config);
    }

    public static function findUsersClipboards($user): array
    {
        return self::findBySQL('user_id = ?', [$user->id]);
    }

    public static function deleteUsersClipboards($user, $type): void
    {
        self::deleteBySQL(
            'user_id = ? AND object_type = ?',
            [$user->id, $type]
        );
    }

}
