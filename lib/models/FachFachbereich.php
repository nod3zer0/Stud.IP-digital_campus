<?php
/**
 * FachFachbereich.php
 * Model class for assignments of Faecher to Fachbereiche (aka institutes).
 * (table mvv_fach_inst)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 *
 * @property array $id alias for pk
 * @property string $fach_id database column
 * @property string $institut_id database column
 * @property int $position database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Fach $fach belongs_to Fach
 * @property Fachbereich $fachbereich belongs_to Fachbereich
 */

class FachFachbereich extends ModuleManagementModel
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_fach_inst';

        $config['belongs_to']['fach'] = [
            'class_name' => Fach::class,
            'foreign_key' => 'fach_id',
            'assoc_func' => 'findCached',
        ];
        $config['belongs_to']['fachbereich'] = [
            'class_name' => Fachbereich::class,
            'foreign_key' => 'institut_id',
            'assoc_func' => 'findCached',
        ];

        parent::configure($config);
    }

    /**
     * Returns all asignments of institutes by given fach_id filteres by
     * optional parameter group.
     *
     * @param string $modul_id The id of the Fach the institutes are assigned to
     * @param string $group Optional group
     * @return array Array of objects
     */
    public static function findByFach($fach_id)
    {
        $params = [$fach_id];
        $ret = [];
        $fach_insts = parent::getEnrichedByQuery('SELECT mfi.* '
                . 'FROM mvv_fach_inst mfi '
                . 'WHERE mfi.fach_id = ? '
                , $params);
        foreach ($fach_insts as $fach_inst) {
            $ret[$fach_inst->institut_id] = $fach_inst;
        }
        return $ret;
    }

    public function validate()
    {
        $ret = parent::validate();
        if ($this->fachbereich->isNew()) {
            throw new Exception(_('Unbekannte Einrichtung'));
        }
        return $ret;
    }
}
