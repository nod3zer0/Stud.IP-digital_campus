<?php

/**
 * ColorValue.class.php
 * model class for table color_values
 *
 * Objects of this class holds a colour's name (its purpose)
 * and the value for the colour.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2018-2019
 * @since       4.5
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias column for colour_id
 * @property string $colour_id database column
 * @property I18NString $description database column
 * @property string $value database column
 * @property int $mkdate database column
 * @property int $chdate database column
 */
class ColourValue extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'colour_values';

        $config['i18n_fields']['description'] = true;

        parent::configure($config);
    }

    /**
     * $colours is an array with all colour values that is filled
     * when a colour is requested.
     */
    protected static $colours;

    /**
     * The find method is overloaded here since the table is usually very
     * small and the colour values are requested often. They are stored
     * in an array and served from there to save database requests.
     */
    public static function find($id)
    {
        if (!is_array(self::$colours)) {
            self::$colours = [];
            //Load all colours:
            $colours = self::findBySql('TRUE');
            foreach ($colours as $colour) {
                self::$colours[$colour->id] = $colour;
            }
        }

        return self::$colours[$id];
    }


    /**
     * DEVELOPER WARNING: Do not rename this method to setValue since setValue
     * is a SimpleORMap reserved method for setting attribute values
     * of a SORM object!
     */
    public function setColourValue($r = 0xff, $g = 0xff, $b = 0xff, $a = 0xff)
    {
        $value = dechex($r) . dechex($g) . dechex($b) . dechex($a);
        $this->value = $value;
    }


    public function __toString()
    {
        $r = $this->value[0] . $this->value[1];
        $g = $this->value[2] . $this->value[3];
        $b = $this->value[4] . $this->value[5];

        //The color values are output as '#RRGGBB'.
        //This way it is compatible with the input type color.
        return mb_strtolower('#' . $r . $g . $b);
    }


    public function toRGBAFunction()
    {
        $r = $this->value[0] . $this->value[1];
        $g = $this->value[2] . $this->value[3];
        $b = $this->value[4] . $this->value[5];
        $a = $this->value[6] . $this->value[7];

        return sprintf(
            'rgba(%s %s %s %s)',
            hexdec($r),
            hexdec($g),
            hexdec($b),
            hexdec($a)
        );
    }
}
