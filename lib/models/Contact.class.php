<?php

/**
 * Contact.class.php - model class for table contact
 *
 * @author      <mlunzena@uos.de>
 * @license GPL 2 or later
 *
 * @property array $id alias for pk
 * @property string $owner_id database column
 * @property string $user_id database column
 * @property int|null $mkdate database column
 * @property string $calendar_permissions database column
 *     An enum with the possible values "", "READ" and "WRITE".
 *     The empty string specifies that no calendar permissions are granted.
 * @property SimpleORMapCollection|StatusgruppeUser[] $group_assignments has_many StatusgruppeUser
 * @property User $owner belongs_to User
 * @property User $friend belongs_to User
 * @property string $mkdate database column
 * @property string $chdate database column
 */
class Contact extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'contact';

        $config['belongs_to']['owner'] = [
            'class_name' => User::class,
            'foreign_key' => 'owner_id'
        ];
        $config['belongs_to']['friend'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id'
        ];

        $config['has_many']['groups'] = [
            'class_name'        => ContactGroupItem::class,
            'assoc_func'        => 'findByContact',
            'foreign_key'       => function ($me) {
                return [$me];
            },
            'assoc_foreign_key' => function ($item, $params) {
                //Nothing else here. But this has to be present
                //so that storing a new contact works.
             },
            'on_store'          => 'store',
            'on_delete'         => 'delete'
        ];

        parent::configure($config);
    }
}
