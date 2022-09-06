<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.1
 *
 * @property int slot_id database column
 * @property int id alias column for slot_id
 * @property string user_id database column
 * @property string event_id database column
 * @property int mkdate database column
 * @property ConsultationSlot slot belongs_to ConsultationSlot
 * @property EventData event belongs_to Event
 */
class ConsultationEvent extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'consultation_events';

        $config['belongs_to']['slot'] = [
            'class_name'  => ConsultationSlot::class,
            'foreign_key' => 'slot_id',
        ];
        $config['has_one']['event'] = [
            'class_name'        => EventData::class,
            'foreign_key'       => 'event_id',
            'assoc_foreign_key' => 'event_id',
            'on_delete'         => 'delete',
        ];

        parent::configure($config);
    }
}
