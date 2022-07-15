<?php

namespace Studip\OAuth2;

use Psr\Container\ContainerInterface;

class SetupInformation
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function encryptionKey(): KeyInformation
    {
        return new KeyInformation($this->container->get('encryption_key'));
    }

    public function privateKey(): KeyInformation
    {
        return new KeyInformation($this->container->get('private_key'));
    }

    public function publicKey(): KeyInformation
    {
        return new KeyInformation($this->container->get('public_key'));
    }
}
