<?php

/**
 *
 * @property array $id alias for pk
 * @property string $object_id database column
 * @property string $user_id database column
 * @property int $plugin_id database column
 * @property int $visitdate database column
 * @property int $last_visitdate database column
 * @property User $user belongs_to User
 */
class ObjectUserVisit extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'object_user_visits';

        $config['belongs_to'] = [
            'user' => [
                'class_name'        => User::class,
                'foreign_key'       => 'user_id',
                'assoc_foreign_key' => 'user_id',
            ]
        ];

        parent::configure($config);
    }
}
