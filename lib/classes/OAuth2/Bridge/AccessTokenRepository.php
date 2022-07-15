<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use Studip\OAuth2\Models\AccessToken;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    use ScopesHelper;

    /**
     * Create a new access token.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param mixed                  $userIdentifier
     *
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        return new AccessTokenEntity($userIdentifier, $scopes, $clientEntity);
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        AccessToken::create([
            'id'         => $accessTokenEntity->getIdentifier(),
            'user_id'    => $accessTokenEntity->getUserIdentifier(),
            'client_id'  => $accessTokenEntity->getClient()->getIdentifier(),
            'scopes'     => $this->formatScopes($accessTokenEntity->getScopes()),
            'revoked'    => 0,
            'expires_at' => $accessTokenEntity->getExpiryDateTime()->getTimestamp(),
        ]);

        // TODO: Logging and metrics
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId): void
    {
        $accesstoken = AccessToken::find($tokenId);
        if ($accesstoken) {
            $accesstoken->revoke();
        }
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param string $tokenId
     *
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId): bool
    {
        $accesstoken = AccessToken::find($tokenId);

        return $accesstoken ? $accesstoken->isRevoked() : true;
    }
}
