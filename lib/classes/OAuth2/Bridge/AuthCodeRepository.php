<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use Studip\OAuth2\Models\AuthCode;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use ScopesHelper;

    /**
     * Creates a new AuthCode.
     */
    public function getNewAuthCode(): AuthCodeEntityInterface
    {
        return new AuthCodeEntity();
    }

    /**
     * Persists a new auth code to permanent storage.
     *
     * @return void
     *
     * @throws UniqueTokenIdentifierConstraintViolationException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        AuthCode::create([
            'id'         => $authCodeEntity->getIdentifier(),
            'user_id'    => $authCodeEntity->getUserIdentifier(),
            'client_id'  => $authCodeEntity->getClient()->getIdentifier(),
            'scopes'     => $this->formatScopes($authCodeEntity->getScopes()),
            'revoked'    => 0,
            'expires_at' => $authCodeEntity->getExpiryDateTime()->getTimestamp(),
        ]);

        // TODO: Logging and metrics
    }

    /**
     * Revoke an auth code.
     *
     * @param string $codeId
     */
    public function revokeAuthCode($codeId): void
    {
        $authCode = AuthCode::find($codeId);
        if ($authCode) {
            $authCode->revoke();
        }
    }

    /**
     * Check if the auth code has been revoked.
     *
     * @param string $codeId
     *
     * @return bool Return true if this code has been revoked
     */
    public function isAuthCodeRevoked($codeId): bool
    {
        $authCode = AuthCode::find($codeId);

        return $authCode ? $authCode->isRevoked() : true;
    }
}
