<?php
/**
 * LtiTool.php - LTI consumer API for Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Elmar Ludwig
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @property int $id database column
 * @property string $name database column
 * @property string $launch_url database column
 * @property string $consumer_key database column
 * @property string $consumer_secret database column
 * @property string $custom_parameters database column
 * @property int $allow_custom_url database column
 * @property int $deep_linking database column
 * @property int $send_lis_person database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property string $oauth_signature_method database column
 * @property SimpleORMapCollection|LtiData[] $links has_many LtiData
 */

class LtiTool extends SimpleORMap
{
    /**
     * Configure the database mapping.
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'lti_tool';

        $config['has_many']['links'] = [
            'class_name'        => LtiData::class,
            'assoc_foreign_key' => 'tool_id',
            'on_delete'         => 'delete'
        ];

        parent::configure($config);
    }

    /**
     * Find all entries.
     */
    public static function findAll()
    {
        return self::findBySQL('1 ORDER BY name');
    }
}
