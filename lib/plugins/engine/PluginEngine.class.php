<?php

/**
 * Factory Class for the plugin engine
 * @author Dennis Reil, <dennis.reil@offis.de>
 * @package pluginengine
 * @subpackage engine
 */

class PluginEngine
{
    /**
     * This function maps an incoming request to a tuple
     * (pluginclassname, unconsumed rest).
     * @param string $dispatch_to
     * @return array the above mentioned tuple
     */
    public static function routeRequest($dispatch_to)
    {
        $dispatch_to = ltrim($dispatch_to, '/');
        if (mb_strlen($dispatch_to) === 0) {
            throw new PluginNotFoundException(_('Es wurde kein Plugin gewählt.'));
        }
        $pos = mb_strpos($dispatch_to, '/');
        return $pos === false
            ? [$dispatch_to, '']
            : [mb_substr($dispatch_to, 0, $pos), mb_substr($dispatch_to, $pos + 1)];
    }

    /**
     * Load the default set of plugins. This currently loads plugins of
     * type Homepage, Standard (if a course is selected), Administration
     * (if user has admin status) and System. The exact type of plugins
     * loaded here may change in the future.
     */
    public static function loadPlugins()
    {
        global $user, $perm;

        // load system plugins
        self::getPlugins('SystemPlugin');

        // load homepage plugins
        self::getPlugins('HomepagePlugin');

        // load course plugins
        if (Context::getId()) {
            self::getPlugins('StudipModule');
            self::getPlugins('StandardPlugin');
        }

        // load admin plugins
        if (is_object($user) && $perm->have_perm('admin')) {
            self::getPlugins('AdministrationPlugin');
        }
    }

    /**
     * Get instance of the plugin specified by plugin class name.
     *
     * @param string $class class name of plugin
     */
    public static function getPlugin ($class)
    {
        return PluginManager::getInstance()->getPlugin($class);
    }

    /**
     * Get instances of all plugins of the specified type. A type of NULL
     * returns all enabled plugins. The optional context parameter can be
     * used to get only plugins that are activated in the given context.
     *
     * @template T
     * @param T $type plugin type or null (all types)
     * @param string $context context range id (optional)
     * @return T[] all plugins of the specified type
     */
    public static function getPlugins ($type, $context = null)
    {
        return PluginManager::getInstance()->getPlugins($type, $context);
    }

    /**
     * Sends a message to all activated plugins of a type and returns an array of
     * the return values.
     *
     * @param  string $type plugin type or null (all types)
     * @param  string $method the method name that should be send to all plugins
     * @param  mixed  a variable number of arguments
     *
     * @return array an array containing the return values
     */
    public static function sendMessage($type, $method /* ... */)
    {
        $args = func_get_args();
        array_splice($args, 1, 0, [null]);
        return call_user_func_array([__CLASS__, 'sendMessageWithContext'], $args);
    }

    /**
     * Sends a message to all activated plugins of a type enabled in a context and
     * returns an array of the return values.
     *
     * @param  string $type plugin type or null (all types)
     * @param  string $context context range id (may be null)
     * @param  string $method the method name that should be send to all plugins
     * @param  mixed  a variable number of arguments
     *
     * @return array      an array containing the return values
     */
    public static function sendMessageWithContext($type, $context, $method /* ... */)
    {
        $args = func_get_args();
        $args = array_slice($args, 3);
        $results = [];
        foreach (self::getPlugins($type, $context) as $plugin) {
            $results[] = call_user_func_array([$plugin, $method], $args);
        }
        return $results;
    }

    /**
    * Generates a URL which can be shown in user interfaces
    * @param StudIPPlugin|string $plugin - the plugin to which should be linked
    * @param array $params - an array with name value pairs
    * @param string $cmd - command to execute by clicking the link
    * @param bool $ignore_registered_params do not add registered params
    * @return string a link to the current plugin with the additional $params
    */
    public static function getURL($plugin, $params = [], $cmd = 'show', $ignore_registered_params = false)
    {
        if (is_null($plugin)) {
            throw new InvalidArgumentException(_('Es wurde kein Plugin gewählt.'));
        } else if (is_object($plugin)) {
            $plugin = mb_strtolower(get_class($plugin)) . '/' . $cmd;
        } else if (mb_strpos($plugin, '/') === false) {
            $plugin = $plugin . '/' . $cmd;
        }

        return URLHelper::getURL('plugins.php/' . $plugin, $params, $ignore_registered_params);
    }

    /**
    * Generates a link (entity encoded URL) which can be shown in user interfaces
    * @param StudIPPlugin|string $plugin - the plugin to which should be linked
    * @param array $params - an array with name value pairs
    * @param string $cmd - command to execute by clicking the link
    * @param bool $ignore_registered_params do not add registeredparams
    * @return string a link to the current plugin with the additional $params
    */
    public static function getLink($plugin, $params = [], $cmd = 'show', $ignore_registered_params = false)
    {
        return htmlReady(self::getURL($plugin, $params, $cmd, $ignore_registered_params));
    }
}
