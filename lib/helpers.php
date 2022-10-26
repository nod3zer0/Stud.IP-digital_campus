<?php

use Psr\Container\ContainerInterface;

/**
 * This function returns the Dependency Injection container used.
 *
 * ```
 * $container = app();
 * ```
 *
 * You may pass a class or interface name to resolve it from the container:
 *
 * ```
 * $logger = app(LoggerInterface::class);
 * ```
 *
 * @param string|null $entryId
 *
 * @return ContainerInterface|mixed either the DI container or the entry associated to the $entryId
 */
function app($entryId = null)
{
    $container = \Studip\DIContainer::getInstance();
    if (is_null($entryId)) {
        return $container;
    }

    return $container->get($entryId);
}
