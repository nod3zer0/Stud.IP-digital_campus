<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use Psr\Container\ContainerInterface;
use Studip\OAuth2\Models\Scope;

class ScopeRepository implements ScopeRepositoryInterface
{
    /** @var array<string, string> */
    private $scopes;

    public function __construct(ContainerInterface $container)
    {
        $this->scopes = Scope::scopes();
    }

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     */
    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        if (!isset($this->scopes[$identifier])) {
            return null;
        }

        return new ScopeEntity($identifier);
    }

    /**
     * Given a client, grant type and optional user identifier validate
     * the set of scopes requested are valid and
     * optionally append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string                 $grantType
     * @param ClientEntityInterface  $clientEntity
     * @param null|string            $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        return array_filter(
            $scopes,
            function ($scope) {
                return isset($this->scopes[$scope->getIdentifier()]);
            }
        );
    }
}
