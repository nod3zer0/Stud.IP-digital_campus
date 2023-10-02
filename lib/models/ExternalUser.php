<?php
/**
 * ExternalUser
 * Model class for blubber users that are not part of auth_user_md5 table
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.0
 *
 * @property string $id alias column for external_contact_id
 * @property string $external_contact_id database column
 * @property string|null $foreign_id database column
 * @property string|null $host_id database column
 * @property string $contact_type database column
 * @property string $name database column
 * @property string|null $avatar_url database column
 * @property JSONArrayObject|null $data database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property OERHost|null $host belongs_to OERHost
 */

class ExternalUser extends SimpleORMap
{
    /**
     * Configures this model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'external_users';
        $config['belongs_to']['host'] = [
            'class_name' => OERHost::class,
            'foreign_key' => 'host_id'
        ];
        $config['serialized_fields']['data'] = JSONArrayObject::class;
        parent::configure($config);
    }
}
