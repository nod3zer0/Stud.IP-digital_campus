<?php
/**
 * AbschlussZuord.php
 * Model class for assignments of Abshluss-Kategorien to Abschluesse
 * (table mvv_abschl_zuord)
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
 * @property string $id alias column for abschluss_id
 * @property string $abschluss_id database column
 * @property string $kategorie_id database column
 * @property int $position database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Abschluss $abschluss belongs_to Abschluss
 * @property AbschlussKategorie $kategorie belongs_to AbschlussKategorie
 */

class AbschlussZuord extends ModuleManagementModel
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_abschl_zuord';

        $config['belongs_to']['abschluss'] = [
            'class_name' => Abschluss::class,
            'foreign_key' => 'abschluss_id',
            'assoc_func' => 'findCached',
        ];
        $config['belongs_to']['kategorie'] = [
            'class_name' => AbschlussKategorie::class,
            'foreign_key' => 'kategorie_id',
            'assoc_func' => 'findCached',
        ];

        parent::configure($config);
    }

}
