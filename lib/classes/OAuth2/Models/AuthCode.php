<?php

namespace Studip\OAuth2\Models;

/**
 * @property int $id
 * @property string $user_id
 * @property string $client_id
 * @property string $scopes
 * @property bool $revoked
 * @property int $expires_at
 * @property int $mkdate
 * @property int $chdate
 */
class AuthCode extends \SimpleORMap
{
    use RevokedHelper;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'oauth2_auth_codes';

        $config['belongs_to']['client'] = [
            'class_name'  => Client::class,
            'foreign_key' => 'client_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name'  => \User::class,
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }
}
