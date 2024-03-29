<?php

/**
 * SeparableRoomItem.class.php - model class for a separable room item
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
 * @package     resources
 * @since       4.5
 *
 * @property array $id alias for pk
 * @property int $separable_room_id database column
 * @property string $room_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SeparableRoom $separable_room belongs_to SeparableRoom
 * @property Room $room belongs_to Room
 */
class SeparableRoomPart extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'separable_room_parts';

        $config['belongs_to']['separable_room'] = [
            'class_name'  => SeparableRoom::class,
            'foreign_key' => 'separable_room_id',
            'assoc_func'  => 'find'
        ];
        $config['belongs_to']['room']           = [
            'class_name'  => Room::class,
            'foreign_key' => 'room_id',
            'assoc_func'  => 'find'
        ];

        parent::configure($config);
    }

    public function getRoomName()
    {
        if ($this->room) {
            return $this->room->name;
        }

        return '';
    }
}
