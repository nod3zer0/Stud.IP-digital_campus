<?php

/**
 * ResourcePropertyDefinition.class.php - model class for resource property definitions
 *
 * The ResourcePropertyDefinition class can be used as a Factory
 * for ResourceProperty objects.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2018
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.1
 *
 * @property string $id alias column for property_id
 * @property string $property_id database column
 * @property string $name database column
 * @property I18NString|null $description database column
 * @property string $type database column
 * @property string $options database column
 * @property int $system database column
 * @property int $info_label database column
 * @property I18NString $display_name database column
 * @property int $searchable database column
 * @property int $range_search database column
 * @property string $write_permission_level database column
 * @property int|null $property_group_id database column
 * @property int|null $property_group_pos database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property ResourcePropertyGroup|null $group belongs_to ResourcePropertyGroup
 */


class ResourcePropertyDefinition extends SimpleORMap
{
    /**
     * This regular expression is used to ensure that position properties
     * are always in a format as specified by the ISO-6709
     * string representation.
     */
    const CRSWGS84_REGEX = '/[+-]\d{1,3}\.\d{1,10}[+-]\d{1,3}\.\d{1,10}[+-]\d{1,5}\.\d{1,10}CRSWGS_84\/$/';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'resource_property_definitions';

        $config['belongs_to']['group'] = [
            'class_name' => ResourcePropertyGroup::class,
            'foreign_key' => 'property_group_id'
        ];

        $config['has_many']['properties'] = [
            'class_name'        => ResourceProperty::class,
            'assoc_foreign_key' => 'property_id',
            'on_delete'         => 'delete',
        ];

        $config['i18n_fields']['display_name'] = true;
        $config['i18n_fields']['description'] = true;

        parent::configure($config);
    }

    public static function findByPropertyGroup($group_id)
    {
        return self::findBySql(
            'property_group_id = :group_id
            ORDER BY property_group_pos ASC, name ASC',
            [
                'group_id' => $group_id
            ]
        );
    }

    /**
     * Returns a list of all defined data types.
     *
     * @return string[] An array containing the names of all defined
     *     resource property types.
     */
    public static function getDefinedTypes()
    {
        return [
            'bool',
            'text',
            'num',
            'select',
            'user',
            'institute',
            'position',
            'fileref',
            'url'
        ];
    }

    /**
     * Returns all available options for this property
     * as an array.
     */
    public function getOptionsArray()
    {
        if ($this->options) {
            return explode(';', $this->options);
        }
        return [];
    }


    public function setOptionsFromArray($array = [])
    {
        if (is_array($array)) {
            $this->options = implode(';', $array);
        } else {
            $this->options = '';
        }
    }

    /**
     * Generates appropriate HTML input elements for this property.
     *
     * @param string $value The value of the HTML input element.
     *
     * @param string $special_name A special name for the HTML input(s).
     *
     * @param bool $with_label Whether a label shall be placed around the
     *     HTML input element(s) or not.
     *
     * @param bool $allow_boolean_false Wheter boolean attributes shall
     *     also include a hidden input field that sets the value to zero
     *     if the checkbox for the boolean attribute is not checked.
     *     Defaults to true.
     *
     * @return string A string containing HTML code.
     */
    public function toHtmlInput(
        $value = '',
        $special_name = '',
        $with_label = false,
        $allow_boolean_false = true,
        $disabled = false
    )
    {
        $label_html_classes = '';
        $type = $this->type;
        $input_name = $special_name
                    ? $special_name
                    : 'properties[' . $this->id . ']';

        if ($type == 'bool') {
            $label_html_classes = 'col-3';
            //Booleans can have one or two input elements,
            //whether a false state shall be selectable or not.
            if ($allow_boolean_false) {
                $input_html = sprintf(
                    '<input type="hidden" name="%1$s" value="0" %2$s %3$s>'
                  . '<input type="checkbox" name="%1$s" value="1" %2$s>',
                    htmlReady($input_name),
                    $value ? 'checked' : '',
                    $disabled ? 'disabled' : ''
                );
            } else {
                $input_html = sprintf(
                    '<input type="checkbox" name="%1$s" value="1" %2$s %3$s>',
                    htmlReady($input_name),
                    $value ? 'checked' : '',
                    $disabled ? 'disabled' : ''
                );
            }
            if ($with_label) {
                return sprintf(
                    '<label %1$s>%2$s %3$s</label>',
                    (
                        $label_html_classes
                        ? 'class="' . htmlReady($label_html_classes) . '"'
                        : ''
                    ),
                    $input_html,
                    htmlReady($this->__toString())
                );
            } else {
                return $input_html;
            }
        } elseif ($type == 'select') {
            $options_html = sprintf(
                '<option value="" %2$s>%1$s</option>',
                _('Bitte wählen'),
                !$value ? 'selected="selected"' : ''
            );
            foreach ($this->getOptionsArray() as $option) {
                $options_html .= sprintf(
                    '<option value="%1$s" %2$s>%1$s</option>',
                    htmlReady($option),
                    $value == $option ? 'selected="selected"' : ''
                );
            }
            if ($with_label) {
                return sprintf(
                    '<label %1$s>%4$s<select name="%2$s">%3$s</select></label>',
                    (
                        $label_html_classes
                        ? 'class="' . htmlReady($label_html_classes) . '"'
                        : ''
                    ),
                    htmlReady($input_name),
                    $options_html,
                    htmlReady($this->__toString())
                );
            } else {
                return sprintf(
                    '<select name="%1$s">%2$s</select>',
                    htmlReady($input_name),
                    $options_html
                );
            }
        } elseif ($type == 'position') {
            $factory = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH']);
            $template = $factory->open('templates/resources/position_attribute_form_part.php');
            $template->set_attribute(
                'input_name',
                $input_name
            );
            $template->set_attribute(
                'latitude',
                $value[0]
            );
            $template->set_attribute(
                'longitude',
                $value[1]
            );
            $template->set_attribute(
                'altitude',
                $value[2]
            );

            return $template->render();
        } elseif ($type == 'user') {
            $search = new QuickSearch($input_name, new StandardSearch('user_id'));
            $search->defaultValue($value, ($value ? get_fullname($value, 'full_rev_username') : ''));
            return sprintf(
                '<label %1$s>%2$s<div class="assigned-user-search-wrapper flex-row">%3$s%4$s</div></label>',
                (
                    $label_html_classes
                    ? 'class="' . htmlReady($label_html_classes) . '"'
                    : ''
                ),
                $this->__toString(),
                $search->render(),
                Icon::create('refresh')->asImg(
                    [
                        'class'           => 'delete-assigned-user-icon enter-accessible',
                        'data-input-name' => $input_name,
                        'title'           => _('Zuorndung entfernen'),
                        'tabindex'        => '0',
                        'aria-role'       => 'button'
                    ]
                )
            );
        } else {
            $input_type = 'text';
            if ($type == 'num') {
                $input_type = 'number';
            }
            if ($with_label) {
                return sprintf(
                    '<label %1$s>%5$s<input type="%2$s" name="%3$s" value="%4$s" %5$s></label>',
                    (
                        $label_html_classes
                        ? 'class="' . htmlReady($label_html_classes) . '"'
                        : ''
                    ),
                    $input_type,
                    htmlReady($input_name),
                    $value,
                    htmlReady($this->__toString()),
                    $disabled ? 'disabled' : ''
                );
            } else {
                return sprintf(
                    '<input type="%1$s" name="%2$s" value="%3$s" %4$s>',
                    $input_type,
                    htmlReady($input_name),
                    $value,
                    $disabled ? 'disabled' : ''
                );
            }
        }
    }

    /**
     * Verifies that a property value (state) is valid for the given
     * resource property definition.
     *
     * @param string $state A state for this property which shall be checked.
     *
     * @throws ResourcePropertyStateException If the state has an invalid value
     *     a ResourcePropertyStateException is thrown.
     *
     * @return bool True, if the state value is valid.
     */
    public function validateState($state = '')
    {
        $invalid_state = false;

        //The type 'text' does not need to be validated since it can have
        //all sorts of data in it.
        if ($this->type == 'bool') {
            if (!in_array($state, ['0', '1'])) {
                //invalid boolean state: neither true nor false
                $invalid_state = true;
            }
        } elseif ($this->type == 'num') {
            if (!preg_match('/[0-9.]+/', $state)) {
                //not a number
                $invalid_state = true;
            }
        } elseif ($this->type == 'user') {
            if (!User::exists($state)) {
                //User does not exist
                throw new ResourcePropertyStateException(
                    sprintf(
                        _('Die Eigenschaft %1$s besitzt einen ungültigen Wert! Der/die Nutzer/-in mit der ID %2$s existiert nicht!'),
                        $this->name,
                        $state
                    )
                );
            }
        } elseif ($this->type == 'institute') {
            if (!Institute::exists($state)) {
                //Institute does not exist
                throw new ResourcePropertyStateException(
                    sprintf(
                        _('Die Eigenschaft %1$s besitzt einen ungültigen Wert! Die Einrichtung mit der ID %2$s existiert nicht!'),
                        $this->name,
                        $state
                    )
                );
            }
        } elseif ($this->type == 'position') {
            if (!preg_match(self::CRSWGS84_REGEX, $state)) {
                //$state does not contain ISO-6709 coordinates
                //in the CRSWGS84 format!
                throw new ResourcePropertyStateException(
                    sprintf(
                        _('Die Positionsangabe für die Eigenschaft %1$s ist ungültig!'),
                        $state
                    )
                );
            }
        }

        //A general exception message:
        if ($invalid_state) {
            throw new ResourcePropertyException(
                sprintf(
                    _('Der Wert %1$s ist für die Eigenschaft %2$s (Typ %3$s) nicht zulässig!'),
                    $state,
                    $this->name,
                    $this->type
                )
            );
        }

        return true;
    }

    /**
     * Creates a ResourceProperty object that is automatically linked
     * to the property definition. The ResourceProperty object is only
     * created but not stored in the database.
     *
     * @param Resource $resource The resource object which shall be extended
     *     by a property.
     * @param string $state The value of the property that shall be created.
     *
     * @throws ResourcePropertyException If $state is invalid for this property.
     *
     * @return ResourceProperty A ResourceProperty object
     *     which can be modified.
     */
    public function createResourceProperty(Resource $resource, $state = '')
    {
        if ($this->validateState($state)) {
            $property = new ResourceProperty();
            $property->property_id = $this->id;
            $property->resource_id = $resource->id;
            $property->state = $state;
            return $property;
        }

        throw new Exception('Could not validate state');
    }

    public function __toString()
    {
        return trim($this->display_name) ?: $this->name;
    }
}
