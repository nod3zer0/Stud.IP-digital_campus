<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/*
 * StudipAuthShib.class.php - Stud.IP authentication against Shibboleth server
 * Copyright (c) 2007  Elmar Ludwig, Universitaet Osnabrueck
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class StudipAuthShib extends StudipAuthSSO
{
    public $env_remote_user = 'HTTP_REMOTE_USER';
    public $local_domain;
    public $session_initiator;
    public $validate_url;
    public $userdata;
    public $username_attribute = 'username';

    /**
     * Constructor: read auth information from remote SP.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!isset($this->plugin_fullname)) {
            $this->plugin_fullname = _('Shibboleth');
        }
        if (!isset($this->login_description)) {
            $this->login_description = _('für Single Sign On mit Shibboleth');
        }

        if (Request::get('sso') === $this->plugin_name && isset($this->validate_url) && isset($_REQUEST['token'])) {
            $context = get_default_http_stream_context($this->validate_url);
            $auth = file_get_contents($this->validate_url . '/' . $_REQUEST['token'], false, $context);

            $this->userdata = json_decode($auth, true);

            if ($this->username_attribute !== 'username') {
                $this->userdata['username'] = $this->userdata[$this->username_attribute];
            }
            if (isset($this->local_domain)) {
                $this->userdata['username'] =
                    str_replace('@' . $this->local_domain, '', $this->userdata['username']);
            }
        }
    }

    /**
     * Return the current username.
     */
    function getUser()
    {
        return $this->userdata['username'];
    }

    /**
     * Return the current URL (including parameters).
     */
    function getURL()
    {
        $url = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $url .= '://';

        if (empty($_SERVER['SERVER_NAME'])) {
            $url .= $_SERVER['HTTP_HOST'];
        } else {
            $url .= $_SERVER['SERVER_NAME'];
        }

        if ($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] != 443 ||
            $_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT'] != 80) {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }

        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * Validate the username passed to the auth plugin.
     * Note: This triggers authentication if needed.
     */
    function verifyUsername($username)
    {
        if (isset($this->userdata)) {
            // use cached user information
            return $this->getUser();
        }

        $remote_user = $_SERVER[$this->env_remote_user];

        if (empty($remote_user)) {
            $remote_user = $_SERVER['REMOTE_USER'];
        }

        if (empty($remote_user) || isset($this->validate_url)) {
            if (Request::get('sso') === $this->plugin_name) {
                // force Shibboleth authentication (lazy session)
                $shib_url = $this->session_initiator;
                $shib_url .= strpos($shib_url, '?') === false ? '?' : '&';
                $shib_url .= 'target=' . urlencode($this->getURL());

                // break redirection loop in case of misconfiguration
                if (strstr($_SERVER['HTTP_REFERER'], 'target=') === false) {
                    header('Location: ' . $shib_url);
                    echo '<html></html>';
                    exit();
                }
            }

            // not authenticated
            return NULL;
        }

        // import authentication information
        $this->userdata['username'] = $remote_user;

        foreach ($_SERVER as $key => $value) {
            if (mb_substr($key, 0, 10) == 'HTTP_SHIB_') {
                $key = mb_strtolower(mb_substr($key, 10));
                $this->userdata[$key] = $value;
            }
        }

        if ($this->username_attribute !== 'username') {
            $this->userdata['username'] = $this->userdata[$this->username_attribute];
        }
        if (isset($this->local_domain)) {
            $this->userdata['username'] =
                str_replace('@' . $this->local_domain, '', $this->userdata['username']);
        }
        return $this->getUser();
    }

    /**
     * Get the user domains to assign to the current user.
     */
    function getUserDomains()
    {
        $user = $this->getUser();
        $pos = mb_strpos($user, '@');

        if ($pos !== false) {
            return [mb_substr($user, $pos + 1)];
        }

        return NULL;
    }

    /**
     * Callback that can be used in user_data_mapping array.
     */
    function getUserData($key)
    {
        $data = explode(';', $this->userdata[$key]);

        return $data[0];
    }
}
