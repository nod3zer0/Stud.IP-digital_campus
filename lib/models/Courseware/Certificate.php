<?php

namespace Courseware;

use \User, \Course;

/**
 * Courseware's certificates.
 *
 * @author  Thomas Hackl <hackl@data-quest.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.3
 *
 * @property string     $id             database column
 * @property string     $user_id        database column
 * @property string     $course_id      database column
 * @property int        $mkdate         database column
 */
class Certificate extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_certificates';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name' => Course::class,
            'foreign_key' => 'course_id',
        ];

        parent::configure($config);
    }

}
