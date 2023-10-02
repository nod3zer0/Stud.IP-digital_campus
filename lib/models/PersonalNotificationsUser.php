<?php

/**
 * @license GPL2 or any later version
 *
 * @property array $id alias for pk
 * @property int $personal_notification_id database column
 * @property string $user_id database column
 * @property int $seen database column
 * @property PersonalNotifications $notification belongs_to PersonalNotifications
 */
class PersonalNotificationsUser extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'personal_notifications_user';

        $config['belongs_to']['notification'] = [
            'class_name'  => PersonalNotifications::class,
            'foreign_key' => 'personal_notification_id'
        ];

        parent::configure($config);
    }
}
