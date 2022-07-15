<?php

namespace Studip\OAuth2;

use DateInterval;
use DI\ContainerBuilder;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use League\OAuth2\Server\Grant\RefreshTokenGrant;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use League\OAuth2\Server\ResourceServer;
use Psr\Container\ContainerInterface;
use Studip\OAuth2\Exceptions\SetupError;

class Container
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->container->get($key);
    }

    public function __construct()
    {
        $containerBuilder = new ContainerBuilder();
        $this->addConfiguration($containerBuilder);
        $this->addDependencies($containerBuilder);
        $this->container = $containerBuilder->build();
    }

    private function addConfiguration(ContainerBuilder $containerBuilder): void
    {
        $basePath = $GLOBALS['STUDIP_BASE_PATH'];
        $containerBuilder->addDefinitions([
            'encryption_key' => $basePath . '/config/oauth2/encryption_key.php',
            'private_key' => $basePath . '/config/oauth2/private.key',
            'public_key' => $basePath . '/config/oauth2/public.key',

            // TODO: use these and more of them
            'tokens_expire_in' => 'P1Y',
            'refresh_tokens_expire_in' => 'P1Y',
        ]);
    }

    private function addDependencies(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->addDefinitions([
            AccessTokenRepositoryInterface::class => \DI\get(Bridge\AccessTokenRepository::class),
            AuthCodeRepositoryInterface::class => \DI\get(Bridge\AuthCodeRepository::class),
            ClientRepositoryInterface::class => \DI\get(Bridge\ClientRepository::class),
            RefreshTokenRepositoryInterface::class => \DI\get(Bridge\RefreshTokenRepository::class),
            ScopeRepositoryInterface::class => \DI\get(Bridge\ScopeRepository::class),

            AuthorizationServer::class => function (
                ContainerInterface $container,
                AccessTokenRepositoryInterface $accessTokenRepository,
                ClientRepositoryInterface $clientRepository,
                ScopeRepositoryInterface $scopeRepository,
                AuthCodeGrant $authCodeGrant,
                RefreshTokenGrant $refreshGrant
            ) {
                $encryptionKeyFile = $container->get('encryption_key');
                $privateKey = $container->get('private_key');
                if (!is_readable($encryptionKeyFile) || !is_readable($privateKey)) {
                    throw new SetupError();
                }

                $encryptionKey = include $encryptionKeyFile;

                $server = new AuthorizationServer(
                    $clientRepository,
                    $accessTokenRepository,
                    $scopeRepository,
                    $privateKey,
                    $encryptionKey
                );

                $server->enableGrantType($authCodeGrant, new DateInterval('PT1H'));
                $server->enableGrantType($refreshGrant, new DateInterval('PT1H'));

                return $server;
            },

            AuthCodeGrant::class => function (
                AuthCodeRepositoryInterface $authCodeRepository,
                RefreshTokenRepositoryInterface $refreshTokenRepository
            ) {
                $grant = new AuthCodeGrant($authCodeRepository, $refreshTokenRepository, new DateInterval('PT10M'));
                $grant->setRefreshTokenTTL(new DateInterval('P1M'));

                return $grant;
            },

            RefreshTokenGrant::class => function (RefreshTokenRepositoryInterface $refreshTokenRepository) {
                $refreshGrant = new RefreshTokenGrant($refreshTokenRepository);
                $refreshGrant->setRefreshTokenTTL(new DateInterval('P1M'));

                return $refreshGrant;
            },

            ResourceServer::class => function (
                ContainerInterface $container,
                AccessTokenRepositoryInterface $accessTokenRepository
            ) {
                $publicKey = $container->get('public_key');
                $resourceServer = new ResourceServer($accessTokenRepository, $publicKey);

                return $resourceServer;
            },
        ]);
    }
}
