<?php
/**
 * This factory retrieves the instance of StudipCache configured for use in
 * this Stud.IP installation.
 *
 * @package    studip
 * @subpackage lib
 *
 * @author    Marco Diedrich (mdiedric@uos)
 * @author    Marcus Lunzenauer (mlunzena@uos.de)
 * @copyright 2007 (c) Authors
 * @since     1.6
 * @license   GPL2 or any later version
 */

class StudipCacheFactory
{
    /**
     * the default cache class
     *
     * @var string
     */
    const DEFAULT_CACHE_CLASS = StudipDbCache::class;

    /**
     * singleton instance
     *
     * @var StudipCache
     */
    private static $cache;


    /**
     * config instance
     *
     * @var Config
     */
    private static $config = null;


    /**
     * Returns the currently used config instance
     *
     * @return Config        an instance of class Config used by this factory to
     *                       determine the class of the actual implementation of
     *                       the StudipCache interface; if no config was set, it
     *                       returns the instance returned by Config#getInstance
     * @see Config
     */
    public static function getConfig()
    {
        return is_null(self::$config) ? Config::getInstance() : self::$config;
    }


    /**
     * @param Config $config an instance of class Config which will be used to
     *                       determine the class of the implementation of interface
     *                       StudipCache
     */
    public static function setConfig($config)
    {
        self::$config = $config;
        self::$cache = NULL;
    }

    /**
     * Resets the configuration and voids the cache instance.
     */
    public static function unconfigure()
    {
        self::$cache = NULL;
    }

    /**
     * Returns a cache instance.
     *
     * @param bool $apply_proxied_operations Whether or not to apply any
     *                                       proxied (disable this in tests!)
     * @return StudipCache the cache instance
     */
    public static function getCache($apply_proxied_operations = true)
    {
        if (is_null(self::$cache)) {
            $proxied = false;

            if (!$GLOBALS['CACHING_ENABLE']) {
                self::$cache = new StudipMemoryCache();

                // Proxy cache operations if CACHING_ENABLE is different from the globally set
                // caching value. This should only be the case in cli mode.
                if (isset($GLOBALS['GLOBAL_CACHING_ENABLE']) && $GLOBALS['GLOBAL_CACHING_ENABLE']) {
                    $proxied = true;
                }
            } else {
                try {
                    $class = self::loadCacheClass();
                    $args = self::retrieveConstructorArguments();

                    self::$cache = self::instantiateCache($class, $args);
                } catch (Exception $e) {
                    error_log(__METHOD__ . ': ' . $e->getMessage());
                    PageLayout::addBodyElements(MessageBox::error(__METHOD__ . ': ' . $e->getMessage()));
                    $class = self::DEFAULT_CACHE_CLASS;
                    self::$cache = new $class();
                }
            }

            // If proxy should be used, inject it. Otherwise apply pending
            // operations, if any.
            if ($proxied) {
                self::$cache = new StudipCacheProxy(self::$cache);
            } elseif ($GLOBALS['CACHING_ENABLE'] && $apply_proxied_operations) {
                // Even if the above condition will try to eliminate most
                // failures, the following operation still needs to be wrapped
                // in a try/catch block. Otherwise there are no means to
                // execute migration 166 which creates the neccessary tables
                // for said operation.
                try {
                    StudipCacheOperation::apply(self::$cache);
                } catch (Exception $e) {
                }
            }
        }

        return self::$cache;
    }


    /**
     * Load configured cache class and return its name.
     *
     * @return string  the name of the configured cache class
     */
    public static function loadCacheClass()
    {
        $cacheConfig = self::getConfig()->SYSTEMCACHE;

        $cache_class = $cacheConfig['type'] ?: null;

        # default class
        if ($cache_class === null) {
            $version = new DBSchemaVersion();
            if ($version->get(1) < 224) {
                // db cache is not yet available, use StudipMemoryCache
                return 'StudipMemoryCache';
            }

            return self::DEFAULT_CACHE_CLASS;
        }

        if (!class_exists($cache_class)) {
            throw new UnexpectedValueException("Could not find class: '$cache_class'");
        }

        return $cache_class;
    }

    /**
     * Return an array of arguments required for instantiation of the cache
     * class.
     *
     * @return array  the array of arguments
     */
    public static function retrieveConstructorArguments()
    {
        $cacheConfig = self::getConfig()->SYSTEMCACHE;

        return $cacheConfig ?: [];
    }

    /**
     * Return an instance of a given class using some arguments. Unless the
     * memory cache is instantiated, the cache will be wrapped in a wrapper
     * class that uses a memory cache to reduce accesses to the cache.
     *
     * @param  string $class     the name of the class
     * @param  array  $arguments an array of arguments to be used by the constructor
     *
     * @return StudipCache  an instance of the specified class
     */
    public static function instantiateCache($class, $arguments)
    {
        $reflection_class = new ReflectionClass($class);
        $cache = (is_array($arguments['config']) && count($arguments['config']) > 0)
               ? $reflection_class->newInstanceArgs($arguments['config'])
               : $reflection_class->newInstance();

        if ($class !== StudipMemoryCache::class) {
            return new StudipCacheWrapper($cache);
        }

        return $cache;
    }
}
