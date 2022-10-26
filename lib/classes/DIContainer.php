<?php

namespace Studip;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class DIContainer
{
    /**
     * The current globally available container.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Get the globally available instance of the container.
     *
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $builder = static::createBuilder();
            static::$instance = $builder->build();
        }

        return static::$instance;
    }

    /**
     * Set the instance of the container.
     *
     * @param  \Psr\Container\ContainerInterface|null  $container
     * @return \Psr\Container\ContainerInterface|static
     */
    public static function setInstance(ContainerInterface $container = null)
    {
        return static::$instance = $container;
    }

    /**
     * Set up the ContainerBuilder.
     */
    protected static function createBuilder(): ContainerBuilder
    {
        $builder = new ContainerBuilder();
        if (\Studip\ENV == 'production') {
            $builder->enableCompilation($GLOBALS['TMP_PATH']);
        }
        $builder->ignorePhpDocErrors(true);
        $builder->addDefinitions('lib/bootstrap-definitions.php');

        return $builder;
    }
}
