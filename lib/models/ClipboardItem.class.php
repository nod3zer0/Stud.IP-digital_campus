<?php
/**
 * ClipboardItem.class.php - model class for clipboard items
 * (Merkzettel-Einträge)
 *
 * The ClipboardItem class holds single items of a clipboard.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 *
 * @property int $id database column
 * @property int $clipboard_id database column
 * @property string $range_id database column
 * @property string $range_type database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Clipboard $clipboard belongs_to Clipboard
 */
class ClipboardItem extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'clipboard_items';

        $config['belongs_to']['clipboard'] = [
            'class_name' => Clipboard::class,
            'foreign_key' => 'clipboard_id',
            'assoc_func' => 'find'
        ];

        parent::configure($config);
    }


    /**
     * @returns string representation of this clipboard item.
     */
    public function __toString()
    {
        //Get the class $range_type and the object with ID $range_id,
        //if $range_type is a StudipItem:

        $use_generic_name = true;
        $object = null;
        if (is_subclass_of($this->range_type, 'StudipItem', true)) {
            $range_class_name = $this->range_type;
            $object = $range_class_name::find($this->range_id);
            if ($object) {
                $use_generic_name = false;
            }
        }

        if ($use_generic_name) {
            //$range_type is not a class name of a StudipItem class
            //or no object of a StudipItem class could be found:
            //We cannot determine the name and must therefore use
            //a generic name:
            return $this->range_type . '_' . $this->range_id;
        } else {
            return $object->getItemName(false);
        }
    }
}
