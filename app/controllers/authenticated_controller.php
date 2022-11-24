<?php
/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class AuthenticatedController extends StudipController
{
    protected $with_session = true;  //we do need to have a session for this controller
    protected $allow_nobody = false; //nobody is not allowed and always gets a login-screen

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Restore request if present
        if (isset($this->flash['request'])) {
            foreach ($this->flash['request'] as $key => $value) {
                Request::set($key, $value);
            }
        }
    }

    protected function keepRequest()
    {
        $this->flash['request'] = Request::getInstance()->getIterator()->getArrayCopy();
    }
}
