<?php
/**
 * LtiGrade.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @property array $id alias for pk
 * @property int $link_id database column
 * @property string $user_id database column
 * @property float $score database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property LtiData $link belongs_to LtiData
 * @property User $user belongs_to User
 */

class LtiGrade extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'lti_grade';

        $config['belongs_to']['link'] = [
            'class_name'  => LtiData::class,
            'foreign_key' => 'link_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }
}
