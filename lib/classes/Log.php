<?php

use Psr\Log\LoggerInterface;

/**
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void emergency(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 */
class Log
{
    /**
     * The underlying logger.
     *
     * @var LoggerInterface
     */
    protected static $instance;

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();

        return $instance->$method(...$args);
    }

    public static function getInstance(): LoggerInterface
    {
        if (!isset(static::$instance)) {
            static::$instance = app(LoggerInterface::class);
        }

        return static::$instance;
    }

    public static function setInstance(LoggerInterface $instance): void
    {
        static::$instance = $instance;
    }
}
