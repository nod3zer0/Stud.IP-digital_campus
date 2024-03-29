<?php

/**
 * ResourceCategoryProperty.class.php - model class for
 * resource category properties
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 *
 * @property array $id alias for pk
 * @property string $category_id database column
 * @property string $property_id database column
 * @property int $requestable database column
 * @property int $protected database column
 * @property int $system database column
 * @property string|null $form_text database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property ResourceCategory $category belongs_to ResourceCategory
 * @property ResourcePropertyDefinition $definition belongs_to ResourcePropertyDefinition
 * @property mixed $name additional field
 * @property mixed $type additional field
 */


class ResourceCategoryProperty extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_category_properties';

        $config['belongs_to']['category'] = [
            'class_name' => ResourceCategory::class,
            'foreign_key' => 'category_id',
            'assoc_func' => 'find'
        ];

        $config['belongs_to']['definition'] = [
            'class_name' => ResourcePropertyDefinition::class,
            'foreign_key' => 'property_id',
            'assoc_func' => 'find'
        ];

        $config['additional_fields']['name'] = ['definition', 'name'];
        $config['additional_fields']['type'] = ['definition', 'type'];

        parent::configure($config);
    }

    public static function findByNameAndCategoryId($name, $category_id)
    {
        return self::findOneBySql(
            'INNER JOIN resource_property_definitions rpd
            USING (property_id)
            WHERE
            rpd.name = :name
            AND
            resource_category_properties.category_id = :category_id',
            [
                'name' => $name,
                'category_id' => $category_id
            ]
        );
    }
}
