<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Studip\OAuth2\Models\RefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * Creates a new refresh token.
     */
    public function getNewRefreshToken(): RefreshTokenEntityInterface
    {
        return new RefreshTokenEntity();
    }

    /**
     * Create a new refresh token_name.
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        RefreshToken::create([
                'id'              => $refreshTokenEntity->getIdentifier(),
                'access_token_id' => $refreshTokenEntity->getAccessToken()->getIdentifier(),
                'revoked'         => 0,
                'expires_at'      => $refreshTokenEntity->getExpiryDateTime()->getTimestamp(),
        ]);

        // TODO: Logging and metrics
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId): void
    {
        $refreshToken = RefreshToken::find($tokenId);
        if ($refreshToken) {
            $refreshToken->revoke();
        }
    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isRefreshTokenRevoked($tokenId): bool
    {
        $refreshToken = RefreshToken::find($tokenId);

        return $refreshToken ? $refreshToken->isRevoked() : true;
    }
}
