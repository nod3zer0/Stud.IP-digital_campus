<?php

/**
 * ResourceRequestProperty.class.php - model class for
 * resource request properties
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.1
 *
 * @property array $id alias for pk
 * @property string $request_id database column
 * @property string $property_id database column
 * @property string|null $state database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property ResourcePropertyDefinition $definition belongs_to ResourcePropertyDefinition
 * @property ResourceRequest $request belongs_to ResourceRequest
 * @property mixed $name additional field
 * @property mixed $display_name additional field
 * @property mixed $type additional field
 */
class ResourceRequestProperty extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_request_properties';

        $config['belongs_to']['definition'] = [
            'class_name' => ResourcePropertyDefinition::class,
            'foreign_key' => 'property_id',
            'assoc_func' => 'find'
        ];

        $config['belongs_to']['request'] = [
            'class_name' => ResourceRequest::class,
            'foreign_key' => 'request_id',
            'assoc_func' => 'find'
        ];

        $config['additional_fields']['name'] = ['definition', 'name'];
        $config['additional_fields']['display_name'] = ['definition', 'display_name'];
        $config['additional_fields']['type'] = ['definition', 'type'];

        parent::configure($config);
    }

    /**
     * Creates a string containing the appropriate value
     * of this resource property.
     *
     * @return string The string representation of this resource property.
     */
    public function __toString()
    {
        $string = '';

        if ($this->type == 'position') {
            $string .= ResourceManager::getPositionString($this);
        } elseif ($this->type == 'bool') {
            $string .= $this->state ? _('ja') : _('nein');
        } elseif ($this->type == 'num') {
            $string .= $this->state;
        } elseif ($this->type == 'user') {
            $user = User::find($this->state);
            $string .= $user->getFullName();
        } else {
            $string .= $this->state;
        }
        return $string;
    }
}
