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
class AccessToken extends \SimpleORMap
{
    use RevokedHelper;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'oauth2_access_tokens';

        $config['belongs_to']['client'] = [
            'class_name'  => Client::class,
            'foreign_key' => 'client_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name'  => \User::class,
            'foreign_key' => 'user_id',
        ];

        $config['has_many']['refresh_tokens'] = [
            'class_name'        => RefreshToken::class,
            'assoc_foreign_key' => 'access_token_id',
            'on_delete'         => 'delete',
            'on_store'          => 'store',
        ];

        parent::configure($config);
    }

    public static function findValidTokens(\User $user)
    {
        return static::findBySQL(
            'user_id = ? AND revoked = ? AND expires_at > ?',
            [$user->id, 0, time()]
        );
    }
}
