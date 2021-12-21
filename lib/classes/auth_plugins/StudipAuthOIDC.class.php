<?php
/*
 * StudipAuthOpenID.class.php - Stud.IP authentication using OpenID Connect
 * Copyright (c) 2021  André Noack <noack@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

use Jumbojett\OpenIDConnectClient;
use Jumbojett\OpenIDConnectClientException;

class StudipAuthOIDC extends StudipAuthSSO
{
    /**
     * @var OpenIDConnectClient
     */
    private $oidc;

    /**
     * @var string
     */
    public $provider_url;
    /**
     * @var string
     */
    public $client_id;
    /**
     * @var string
     */
    public $client_secret;


    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        if (Request::get('sso') === $this->plugin_name) {
            $this->oidc = new OpenIDConnectClient($this->provider_url, $this->client_id, $this->client_secret);
            if (isset($this->ssl_options)) {
                foreach ($this->ssl_options as $option_key => $option_value) {
                    if (isset($option_value)) {
                        $this->oidc->{'set' . $option_key}($option_value);
                    }
                }
                if (Config::get()->HTTP_PROXY) {
                    $this->oidc->setHttpProxy(Config::get()->HTTP_PROXY);
                }
                $return_url = URLHelper::getScriptURL($GLOBALS['ABSOLUTE_URI_STUDIP'] . 'index.php', ['sso' => $this->plugin_name, 'again' => 'yes']);
                $this->oidc->setRedirectURL($return_url);
                $this->oidc->addScope(['openid', 'email', 'profile']);
            }
        }
    }

    /**
     * Validate the username passed to the auth plugin.
     *
     * @param string $username
     *
     * @return  string  username openid attribute user_id@domain
     *
     * @throws OpenIDConnectClientException
     */
    public function verifyUsername($username)
    {

        $this->oidc->authenticate();
        $this->userdata = (array)$this->oidc->requestUserInfo();
        if (isset($this->userdata['sub'])) {
            return $this->userdata['username'] = $this->userdata['sub'] . '@' . $this->domain;
        } else {
            return null;
        }
    }

    /**
     * Return the current username of the pending authentication request.
     */
    public function getUser()
    {
        return $this->userdata['username'];
    }

    /**
     * Get the user domains to assign to the current user (if any).
     *
     * @return array    array of user domain names
     */
    public function getUserDomains()
    {
        return $this->domain ? [$this->domain] : null;
    }

    /**
     * Callback that can be used in user_data_mapping array.
     *
     * @see https://openid.net/specs/openid-connect-basic-1_0.html#StandardClaims
     *
     * @param string  key
     *
     * @return  string  parameter value (null if not set)
     */
    public function getUserData($key)
    {
        return $this->userdata[$key];
    }
}
