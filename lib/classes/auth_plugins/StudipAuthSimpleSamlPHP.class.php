<?php
//require_once('/var/simplesamlphp/src/_autoload.php');
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

require_once('/var/simplesamlphp/src/_autoload.php');

class StudipAuthSimpleSamlPHP extends StudipAuthSSO
{


    public $local_domain;
    public $session_initiator;
    public $validate_url;
    public $userdata;
    public $username_attribute = 'username';
    public $auth;
    public $as;

    /**
     * Constructor: read auth information from remote SP.
     */
    public function __construct($config = [])
    {
	    
//	$authi = new \SimpleSAML\Auth\Simple('default-sp');
//	\SimpleSAML\Session::getSessionFromRequest()->cleanup();
       // $session = \SimpleSAML\Session::getSessionFromRequest();
       // $session->cleanup();

        parent::__construct($config);
            if (Request::get('sso') === $this->plugin_name) {

  	     $this->as = new \SimpleSAML\Auth\Simple('default-sp');

if (!$this->as->isAuthenticated()) {
	$this->as->requireAuth(['ReturnTo' => 'https://studip.ceskar.xyz/dispatch.php/start']);

//	print_r("asdsaddsa:w");
}	
	$this->userdata = [];
    	$this->userdata['username'] = $this->as->getAuthData('saml:sp:NameID')->getValue();	

    	$this->userdata['role'] = "admin";	


	$session = \SimpleSAML\Session::getSessionFromRequest();
        $session->cleanup();
	    }
	    
        if (!isset($this->plugin_fullname)) {
            $this->plugin_fullname = _('Federated');
        }
        if (!isset($this->login_description)) {
            $this->login_description = _('Login trough your institution');
	}




		

    }

    /**
     * Return the current username.
     */
    public function getUser()
    {
        return $this->userdata['username'];
    }

    /**
     * Validate the username passed to the auth plugin.
     * Note: This triggers authentication if needed.
     */
    public function verifyUsername($username)
    {
       if (isset($this->userdata)) {
            // use cached user information
            return $this->getUser();
        }

	
if (!$this->as->isAuthenticated()) {
	$this->as->requireAuth(['ReturnTo' => 'https://studip.ceskar.xyz/dispatch.php/start']);
}	
    	$this->userdata['username'] = $this->as->getAuthData('saml:sp:NameID')->getValue();	
	$session = \SimpleSAML\Session::getSessionFromRequest();
        $session->cleanup();
        return $this->getUser();
    }

    /**
     * Get the user domains to assign to the current user.
     */
    function getUserDomains()
    {
	    return NULL;
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

        return $this->userdata[$key];
    }


   public function logout()
    {
        // do a global cas logout
   
	$this->as->Logout(['ReturnTo' => 'https://studip.ceskar.xyz/dispatch.php/start']);
   } 
}
