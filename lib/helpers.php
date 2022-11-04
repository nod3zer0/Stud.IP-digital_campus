<?php

use Psr\Container\ContainerInterface;

/**
 * This function returns the Dependency Injection container used.
 *
 * ```
 * $container = app();
 * ```
 *
 * You may pass an entry name, a class or interface name to resolve it from the container:
 *
 * ```
 * $logger = app(LoggerInterface::class);
 * ```
 *
 * @param string|null $entryId    entry name or a class name
 * @param array       $parameters Optional parameters to use to build the entry.
 *                                Use this to force specific parameters to specific values.
 *                                Parameters not defined in this array will be resolved using the container.
 *
 * @return ContainerInterface|mixed either the DI container or the entry associated to the $entryId
 */
function app($entryId = null, $parameters = [])
{
    $container = \Studip\DIContainer::getInstance();
    if (is_null($entryId)) {
        return $container;
    }

    return $container->make($entryId, $parameters);
}
