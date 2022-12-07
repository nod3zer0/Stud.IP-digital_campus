<?php

/**
 * @property array $id
 * @property string $object_id
 * @property string $user_id
 * @property int $plugin_id
 * @property int $visitdate
 * @property int $last_visitdate
 * @property User $user
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
