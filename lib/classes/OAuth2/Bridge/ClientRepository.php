<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use Studip\OAuth2\Models\Client;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string $clientIdentifier The client's identifier
     */
    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        $sorm = Client::findActive($clientIdentifier);
        if (!$sorm) {
            return null;
        }

        return new ClientEntity(
            $clientIdentifier,
            $sorm['name'],
            explode(',', $sorm['redirect']),
            $sorm->confidential()
        );
    }

    /**
     * Validate a client's secret.
     *
     * @param string      $clientIdentifier The client's identifier
     * @param string|null $clientSecret     The client's secret (if sent)
     * @param string|null $grantType        The type of grant the client is using (if sent)
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        if (!in_array($grantType, ['authorization_code', 'refresh_token'])) {
            return false;
        }

        $client = Client::findActive($clientIdentifier);
        if (!$client) {
            return false;
        }

        return !$client->confidential() || $this->verifySecret((string) $clientSecret, $client->secret);
    }

    /**
     * @param string $clientSecret
     * @param string $storedHash
     */
    protected function verifySecret($clientSecret, $storedHash): bool
    {
        return password_verify($clientSecret, $storedHash);
    }
}
