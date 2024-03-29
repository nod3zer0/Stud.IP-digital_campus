<?php
/**
 * MvvOverlappingConflict.class.php - model class for table mvv_ovl_conflicts
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2018 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 *
 * @property int $id alias column for conflict_id
 * @property int $conflict_id database column
 * @property int $selection_id database column
 * @property string $base_abschnitt_id database column
 * @property string $base_modulteil_id database column
 * @property string $base_course_id database column
 * @property string $base_metadate_id database column
 * @property string $comp_abschnitt_id database column
 * @property string $comp_modulteil_id database column
 * @property string $comp_course_id database column
 * @property string $comp_metadate_id database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property MvvOverlappingSelection $selection belongs_to MvvOverlappingSelection
 * @property StgteilAbschnitt $base_abschnitt belongs_to StgteilAbschnitt
 * @property Modulteil $base_modulteil belongs_to Modulteil
 * @property SeminarCycleDate $base_cycle belongs_to SeminarCycleDate
 * @property Course $base_course belongs_to Course
 * @property StgteilAbschnitt $comp_abschnitt belongs_to StgteilAbschnitt
 * @property Modulteil $comp_modulteil belongs_to Modulteil
 * @property SeminarCycleDate $comp_cycle belongs_to SeminarCycleDate
 * @property Course $comp_course belongs_to Course
 */

class MvvOverlappingConflict extends SimpleORMap
{
    /**
     * Configures the model.
     *
     * @param array  $config Configuration
     */
    protected static function configure($config = array()) {

        $config['db_table'] = 'mvv_ovl_conflicts';
        $config['belongs_to']['selection'] = [
            'class_name'  => MvvOverlappingSelection::class,
            'foreign_key' => 'selection_id',
            'assoc_foreign_key' => 'id'
        ];
        $config['belongs_to']['base_abschnitt'] = [
            'class_name'  => StgteilAbschnitt::class,
            'foreign_key' => 'base_abschnitt_id'
        ];
        $config['belongs_to']['base_modulteil'] = [
            'class_name'  => Modulteil::class,
            'foreign_key' => 'base_modulteil_id'
        ];
        $config['belongs_to']['base_cycle'] = [
            'class_name'  => SeminarCycleDate::class,
            'foreign_key' => 'base_metadate_id'
        ];
        $config['belongs_to']['base_course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'base_course_id'
        ];
        $config['belongs_to']['comp_abschnitt'] = [
            'class_name'  => StgteilAbschnitt::class,
            'foreign_key' => 'comp_abschnitt_id'
        ];
        $config['belongs_to']['comp_modulteil'] = [
            'class_name'  => Modulteil::class,
            'foreign_key' => 'comp_modulteil_id'
        ];
        $config['belongs_to']['comp_cycle'] = [
            'class_name'  => SeminarCycleDate::class,
            'foreign_key' => 'comp_metadate_id'
        ];
        $config['belongs_to']['comp_course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'comp_course_id'
        ];

        parent::configure($config);
    }

    /**
     * Returns true if this conflict belongs to a excluded (hidden) course.
     *
     * @return boolean True if this conflict is excluded.
     */
    public function isExcluded()
    {
        return MvvOverlappingExclude::find([$this->selection->selection_id,
            $this->comp_course_id]) ? true : false;
    }

    /**
     * Deletes all conflicts by given selection id.
     *
     * @param string $selection_id
     * @return number
     */
    public static function deleteBySelection($selection_id)
    {
        return self::deleteBySQL('INNER JOIN `mvv_ovl_selections`
            ON `mvv_ovl_selections`.`id` = `mvv_ovl_conflicts`.`selection_id`
            WHERE `mvv_ovl_selections`.`selection_id` = ?',
            [$selection_id]);
    }
}
