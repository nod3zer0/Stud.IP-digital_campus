<?php
/**
 * ExternPageConfig.php - model class for the configuration of external pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     extern
 * @since       5.4
 *
 * @property string $config_id database column
 * @property string $id alias column for config_id
 * @property string $range_id database column
 * @property string $type database column
 * @property string $name database column
 * @property string $description database column
 * @property string $conf database column
 * @property string $template database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property User $author has_one User
 * @property User $editor has_one User
 */

class ExternPageConfig extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'extern_pages_configs';

        $config['belongs_to']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'author_id'
        ];
        $config['belongs_to']['editor'] = [
            'class_name'  => User::class,
            'foreign_key' => 'editor_id'
        ];
        $config['belongs_to']['range'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'range_id'
        ];

        $config['serialized_fields']['conf'] = JSONArrayObject::class;

        parent::configure($config);
    }
}

