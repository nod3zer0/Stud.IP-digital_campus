<?php

namespace Studip\OAuth2\Models;

/**
 * @property int $id
 * @property string $access_token_id
 * @property string $client_id
 * @property bool $revoked
 * @property int $expires_at
 */
class RefreshToken extends \SimpleORMap
{
    use RevokedHelper;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'oauth2_refresh_tokens';

        $config['belongs_to']['access_token'] = [
            'class_name'  => AccessToken::class,
            'foreign_key' => 'access_token_id',
        ];

        parent::configure($config);
    }

    /**
     * Revokes refresh tokens by access token id.
     *
     * @param string $tokenId
     */
    public static function revokeByAccessTokenId($tokenId): void
    {
        $refreshTokens = self::findBySQL('access_token_id = ?', [$tokenId]);
        foreach ($refreshTokens as $refreshToken) {
            $refreshToken->revoke();
        }
    }
}
