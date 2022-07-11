<?php
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Stud.IP authentication against CAS Server
 *
 * @access   public
 * @author   Dennis Reil <dennis.reil@offis.de>
 * @package
 */

require_once 'composer/jasig/phpcas/CAS.php';
require_once 'lib/classes/cas/CAS_PGTStorage_Cache.php';

class StudipAuthCAS extends StudipAuthSSO
{

    public $host;
    public $port;
    public $uri;
    public $cacert;

    public $cas;
    public $userdata;

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
        if (!isset($this->plugin_fullname)) {
            $this->plugin_fullname = _('CAS');
        }
        if (!isset($this->login_description)) {
            $this->login_description = _('für Single Sign On mit CAS');
        }
        if (Request::get('sso') === $this->plugin_name) {
            $this->cas = new CAS_Client(CAS_VERSION_2_0, $this->proxy, $this->host, $this->port, $this->uri, false);

            if ($this->proxy) {
                URLHelper::setBaseUrl($GLOBALS['ABSOLUTE_URI_STUDIP']);
                $this->cas->setPGTStorage(new CAS_PGTStorage_Cache($this->cas));
                $this->cas->setCallbackURL(URLHelper::getURL('dispatch.php/cas/proxy'));
            }

            if (isset($this->cacert)) {
                $this->cas->setCasServerCACert($this->cacert);
            } else {
                $this->cas->setNoCasServerValidation();
            }
        }
    }

    /**
     * Return the current username.
     */
    function getUser()
    {
        return $this->cas->getUser();
    }

    /**
     * Validate the username passed to the auth plugin.
     * Note: This triggers authentication if needed.
     */
    function verifyUsername($username)
    {
        $this->cas->forceAuthentication();
        return $this->getUser();
    }

    function getUserData($key)
    {
        $userdataclassname = $this->user_data_mapping_class;
        if (!class_exists($userdataclassname)) {
            Log::ERROR($this->plugin_name . ': no userdataclassname specified or found.');
            return;
        }
        // get the userdata
        if (empty($this->userdata)) {
            $this->userdata = new $userdataclassname();
        }
        return $this->userdata->getUserData($key, $this->cas->getUser());
    }

    function logout()
    {
        // do a global cas logout
        $this->cas = new CAS_Client(CAS_VERSION_2_0, false, $this->host, $this->port, $this->uri, false);
        $this->cas->logout();
    }
}
