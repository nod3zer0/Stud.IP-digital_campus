<?php

/**
 * Class: StudipAuthSimpleSamlPHP
 * author: Rene Ceska <xceska06@stud.fit.vutbr.cz>
 * This class is used to authenticate users through SimpleSAMLphp.
 * It is designed specificaly to use with BUT FIT SimpleSAMLphp IdP.
 * But it can be easily modified to work with any other IdP.
 * This code was inspired by other Stud.IP auth plugins.
 */

require_once('/var/simplesamlphp/src/_autoload.php');

class StudipAuthSimpleSamlPHP extends StudipAuthSSO
{
    // Url to redirect to after successful login
    public $return_to_url;
    // Name of the SimpleSAMLphp SP
    public $sp_name;
    // Name of attribute that contains username
    public $username_attribute;
    public $userdata;
    public $as;


    /**
     * Constructor: read auth information from remote SP.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        // check if user chosen to login through this plugin
        if (Request::get('sso') === $this->plugin_name) {

            $this->as = new \SimpleSAML\Auth\Simple($this->sp_name);

            // check if user is already authenticated and if not, authenticate them
            if (!$this->as->isAuthenticated()) {
                $this->as->requireAuth(['ReturnTo' => $this->return_to_url]);
            }
            $this->userdata = [];
            // get username
            $this->userdata['username'] =  $this->as->getAttributes()[$this->username_attribute][0];
            // get other user attributes
            $this->userdata = array_merge($this->userdata, $this->as->getAttributes());

            // cleanup session so it does not interfere with Stud.IP session
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

        // check if user is already authenticated and if not, authenticate them
        if (!$this->as->isAuthenticated()) {
            $this->as->requireAuth(['ReturnTo' => $this->return_to_url]);
        }
        $this->userdata['username'] =  $this->as->getAttributes()[$this->username_attribute][0];
        $session = \SimpleSAML\Session::getSessionFromRequest();
        $session->cleanup();
        return $this->getUser();
    }

    /**
     * Get the user domains to assign to the current user.
     */
    function getUserDomains()
    {
        //not needed for BUT FIT IdP
        return NULL;
    }

    /**
     * Callback that can be used in user_data_mapping array.
     */
    function getUserData($key)
    {
        return $this->userdata[$key];
    }

    /**
     * Callback that can be used in user_data_mapping array. This function is used for BUT FIT IdP.
     */
    function getButFitUserData($key)
    {
        return $this->userdata[$key][0];
    }

    /**
     * Assign roles to user based on their attributes. This function is used for BUT FIT IdP.
     */
    function assignButFitRoles()
    {
        $roles = $this->userdata['urn:oid:1.3.6.1.4.1.5923.1.1.1.9'];

        if (in_array("student@vutbr.cz", $roles)) {
            $role = "dozent";
        } else {
            $role = "author";
        }
        return $role;
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $auth = new \SimpleSAML\Auth\Simple($this->sp_name);
        $auth->Logout();
    }
}
