<?php
/**
 * Copyright (c) 2014  Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class PluginController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        if (!isset($dispatcher->current_plugin)) {
            throw new Exception('Plugin missing for plugin controller!');
        }
        $this->plugin = $dispatcher->current_plugin;

        if ($this->plugin && $this->plugin->hasTranslation()) {
            // Localization
            $this->_ = function ($string) {
                return call_user_func_array(
                    [$this->plugin, '_'],
                    func_get_args()
                );
            };

            $this->_n = function ($string0, $tring1, $n) {
                return call_user_func_array(
                    [$this->plugin, '_n'],
                    func_get_args()
                );
            };
        }
    }

    /**
     * Creates the body element id for this controller a given action.
     *
     * @param string $unconsumed_path Unconsumed path to extract action from
     * @return string
     */
    protected function getBodyElementIdForControllerAndAction($unconsumed_path)
    {
        $body_id = implode('-', [
            'plugin',
            strtosnakecase(get_class($this->plugin)),
            parent::getBodyElementIdForControllerAndAction($unconsumed_path),
        ]);

        return $body_id;
    }

    /**
     * Intercepts all non-resolvable method calls in order to correctly handle
     * calls to _ and _n.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (isset($this->_template_variables[$method]) && is_callable($this->_template_variables[$method])) {
            return call_user_func_array($this->_template_variables[$method], $arguments);
        }
        return parent::__call($method, $arguments);
    }
}
