<?php
/**
 * SemesterCourse.class.php
 * Contains the SemesterCourse model.
 *
 * This class represents entries in the mapping table
 * seminare_semester.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property array $id alias for pk
 * @property string $semester_id database column
 * @property string $course_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Semester $semester belongs_to Semester
 * @property Course $course belongs_to Course
 */
class SemesterCourse extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'semester_courses';

        $config['belongs_to']['semester'] = [
            'class_name'  => Semester::class,
            'foreign_key' => 'semester_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'course_id',
        ];

        parent::configure($config);
    }
}
