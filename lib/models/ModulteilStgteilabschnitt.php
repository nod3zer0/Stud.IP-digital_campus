<?php
/**
 * ModulteilStgteilabschnitt.php
 * Model class for the relation between Modulteile and
 * Studiengangteil-Abschnitte (table mvv_modulteil_stgteilabschnitt)
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
 * @property string $modulteil_id database column
 * @property string $abschnitt_id database column
 * @property int $fachsemester database column
 * @property string $differenzierung database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Modulteil $modulteil belongs_to Modulteil
 * @property StgteilAbschnitt $abschnitt belongs_to StgteilAbschnitt
 */

class ModulteilStgteilabschnitt extends ModuleManagementModel
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modulteil_stgteilabschnitt';

        $config['belongs_to']['modulteil'] = [
            'class_name' => Modulteil::class,
            'foreign_key' => 'modulteil_id',
            'assoc_func' => 'findCached',
        ];
        $config['belongs_to']['abschnitt'] = [
            'class_name' => StgteilAbschnitt::class,
            'foreign_key' => 'abschnitt_id',
            'assoc_func' => 'findCached',
        ];

        parent::configure($config);
    }

    public function __construct($id = null)
    {
        parent::__construct($id);
        $this->object_real_name =
                _('Zuordnung Modulteil zu Studiengangteil-Abschnitt');
    }

    /**
     * @see ModuleManagementModel::getClassDisplayName
     */
    public static function getClassDisplayName($long = false)
    {
        return _('Zuordnung Modulteil zu Studiengangteil-Abschnitt');
    }

     /**
     * Inherits the status of the parent StgteilAbschnitt.
     *
     * @return string the status of parent StgteilAbschnitt
     */
    public function getStatus()
    {
        if ($this->abschnitt) {
            return $this->abschnitt->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}
