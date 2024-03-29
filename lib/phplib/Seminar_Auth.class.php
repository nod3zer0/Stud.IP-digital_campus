<?php

/**
 * Seminar_Auth.class.php
 *
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2000 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 */
class Seminar_Auth
{
    /**
     * @var string
     */
    public $classname;

    /**
     * @var string
     */
    public $error_msg = "";

    /**
     * @var array
     */
    protected $persistent_slots = ["auth", "classname"];

    /**
     * @var bool
     */
    protected $nobody = false; ## If true, a default auth is created...

    /**
     * @var string
     */
    protected $cancel_login = "cancel_login"; ## The name of a button that can be
    ## used to cancel a login form
    /**
     * @var array
     */
    public $auth = []; ## Data array

    public $need_email_activation = null;

    /**
     *
     */
    function __construct()
    {
        $this->classname = get_class($this);
    }

    /**
     * @param $f
     * @return $this
     */
    function check_feature($f)
    {
        if ($this->classname != $f) {
            $clone = new $f;
            $clone->auth = $this->auth;
            return $clone;
        } else {
            return $this;
        }
    }

    /**
     * Check current auth state. Should be one of
     * 1) Not logged in (no valid auth info or auth expired)
     * 2) Logged in (valid auth info)
     * 3) Login in progress (if $this->cancel_login, revert to state 1)

     * @return int
     */
    protected function getState(): int
    {
        if ($this->is_authenticated()) {
            $uid = $this->auth['uid'];
            switch ($uid) {
                case 'form':
                    # Login in progress
                    if (Request::option($this->cancel_login)) {
                        # If $this->cancel_login is set, delete all auth info and set
                        # state to "Not logged in", so eventually default or automatic
                        # authentication may take place
                        $this->unauth();
                        $state = 1;
                    } else {
                        # Set state to "Login in progress"
                        $state = 3;
                    }
                    break;
                default:
                    # User is authenticated and auth not expired
                    $state = 2;
                    break;
            }
        } else {
            # User is not (yet) authenticated
            $this->unauth();
            $state = 1;
        }

        return $state;
    }

    /**
     * @return bool
     * @throws RuntimeException
     */
    public function start()
    {
        global $sess;

        switch ($this->getState()) {
            case 1:
                # No valid auth info or auth is expired

                # Check for user supplied automatic login procedure
                if ($uid = $this->auth_preauth()) {
                    $this->auth["uid"] = $uid;
                    $sess->regenerate_session_id(['auth', '_language', 'phpCAS', 'contrast']);
                    $sess->freeze();
                    $GLOBALS['user'] = new Seminar_User($this->auth['uid']);
                    return true;
                }

                if ($this->nobody) {
                    # Authenticate as nobody
                    $this->auth["uid"] = "nobody";
                    return true;
                } else {
                    # Show the login form
                    $this->auth_loginform();
                    $this->auth["uid"] = "form";
                    $sess->freeze();
                    exit;
                }
            case 2:
                # Valid auth info
                # do nothin
                break;
            case 3:
                # Login in progress, check results and act accordingly
                $uid = $this->auth_validatelogin();
                if ($uid) {
                    $this->auth["uid"] = $uid;
                    $keep_session_vars = ['auth', 'forced_language', '_language', 'contrast', 'oauth2'];
                    if ($this->auth['perm'] === 'root') {
                        $keep_session_vars[] = 'plugins_disabled';
                    }
                    $sess->regenerate_session_id($keep_session_vars);
                    $sess->freeze();
                    $GLOBALS['user'] = new Seminar_User($this->auth['uid']);
                    return true;
                } else {
                    $this->auth_loginform();
                    $this->auth["uid"] = "form";
                    $sess->freeze();
                    exit;
                }
            default:
                # This should never happen. Complain.
                throw new RuntimeException("Error in auth handling: invalid state reached.");
        }

        return false;
    }


    /**
     * @return array
     */
    function __sleep()
    {
        return $this->persistent_slots;
    }


    /**
     *
     */
    function unauth()
    {
        $this->auth = [];
        $this->auth["uid"] = "";
        $this->auth["perm"] = "";
    }


    /**
     *
     */
    function logout()
    {
        $_SESSION['auth'] = null;
        $this->unauth();
        $GLOBALS['auth'] = $this;
    }

    /**
     * @param $ok
     * @return bool
     */
    function login_if($ok)
    {
        if ($ok) {
            $this->unauth(); # We have to relogin, so clear current auth info
            $this->nobody = false; # We are forcing login, so default auth is
            # disabled
            $this->start(); # Call authentication code
        }
        return true;
    }

    /**
     * @return bool
     * @throws AccessDeniedException
     */
    function is_authenticated()
    {
        $cfg = Config::GetInstance();
        //check if the user got kicked meanwhile, or if user is locked out
        if (!empty($this->auth['uid']) && !in_array($this->auth['uid'], ['form', 'nobody'])) {
            $user = null;
            if (isset($GLOBALS['user']) && $GLOBALS['user']->id == $this->auth['uid']) {
                $user = $GLOBALS['user']->getAuthenticatedUser();
            } else {
                $user = User::find($this->auth['uid']);
            }
            if (!$user->username || $user->isBlocked()) {
                $this->unauth();
            }
        } elseif ($cfg->getValue('MAINTENANCE_MODE_ENABLE') && Request::username('loginname')) {
            $user = User::findByUsername(Request::username('loginname'));
        }
        if ($cfg->getValue('MAINTENANCE_MODE_ENABLE') && $user->perms != 'root') {
            $this->unauth();
            throw new AccessDeniedException(_("Das System befindet sich im Wartungsmodus. Zur Zeit ist kein Zugriff möglich."));
        }
        return @$this->auth['uid'] ? : false;
    }

    /**
     * @return bool
     */
    function auth_preauth()
    {
        // is Single Sign On activated?
        if (($provider = Request::option('sso'))) {

            $this->check_environment();

            Metrics::increment('core.sso_login.attempted');

            // then do login
            if (($authplugin = StudipAuthAbstract::GetInstance($provider))) {
                $user = $authplugin->authenticateUser('', '');
                if ($user) {
                    if ($user->isExpired()) {
                        throw new AccessDeniedException(_('Dieses Benutzerkonto ist abgelaufen. Wenden Sie sich bitte an die Administration.'));
                    }
                    if ($user->locked == 1) {
                        throw new AccessDeniedException(_('Dieser Benutzer ist gesperrt! Wenden Sie sich bitte an die Administration.'));
                    }
                    $this->auth["jscript"] = true;
                    $this->auth["perm"] = $user->perms;
                    $this->auth["uname"] = $user->username;
                    $this->auth["auth_plugin"] = $user->auth_plugin;
                    $this->auth_set_user_settings($user);

                    Metrics::increment('core.sso_login.succeeded');

                    return $user->id;
                } else {
                    PageLayout::postMessage(MessageBox::error($authplugin->plugin_name . ': ' . _('Login fehlgeschlagen'), $authplugin->error_msg ? [$authplugin->error_msg] : []),md5($authplugin->error_msg));
                }
            }
        }

        return false;
    }

    /**
     *
     */
    function auth_loginform()
    {
        if (Request::isXhr()) {
            if (Request::isDialog()) {
                header('X-Location: ' . URLHelper::getURL($_SERVER['REQUEST_URI']));
                page_close();
                die();
            }
            throw new AccessDeniedException();
        }

        if (Request::submitted('user_config_submitted')) {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('unset_contrast')) {
                $_SESSION['contrast'] = 0;
            }
            if (Request::submitted('set_contrast')) {
                $_SESSION['contrast'] = 1;
            }


            foreach (array_keys($GLOBALS['INSTALLED_LANGUAGES']) as $language_key) {
                if (Request::submitted('set_language_' . $language_key)) {
                    $_SESSION['forced_language'] = $language_key;
                    $_SESSION['_language'] = $language_key;
                }
            }
        }

        $this->check_environment();

        PageLayout::setBodyElementId('login');

        // load the default set of plugins
        PluginEngine::loadPlugins();

        if (Request::get('loginname') && !$_COOKIE[get_class($GLOBALS['sess'])]) {
            $login_template = $GLOBALS['template_factory']->open('nocookies');
        } else if (isset($this->need_email_activation)) {
            $this->unauth();
            header('Location: ' . URLHelper::getURL('activate_email.php?cancel_login=1&key=&uid=' . $this->need_email_activation));
            page_close();
            die();
        } else {
            unset($_SESSION['semi_logged_in']); // used by email activation
            $login_template = $GLOBALS['template_factory']->open('loginform');
            $login_template->set_attribute('loginerror', (isset($this->auth["uname"]) && $this->error_msg));
            $login_template->set_attribute('error_msg', $this->error_msg);
            $login_template->set_attribute('uname', (isset($this->auth["uname"]) ? $this->auth["uname"] : Request::username('loginname')));
            $login_template->set_attribute('self_registration_activated', Config::get()->ENABLE_SELF_REGISTRATION);

            $query = "SHOW TABLES LIKE 'login_faq'";
            $result = DBManager::get()->query($query);

            if ($result && $result->rowCount() > 0) {
                $login_template->set_attribute('faq_entries', LoginFaq::findBySQL("1"));
            }
        }
        PageLayout::setHelpKeyword('Basis.AnmeldungLogin');
        $header_template = $GLOBALS['template_factory']->open('header');
        $header_template->current_page = _('Login');
        $header_template->link_params = ['cancel_login' => 1];

        include 'lib/include/html_head.inc.php';
        echo $header_template->render();
        echo $login_template->render();
        include 'lib/include/html_end.inc.php';
        page_close();
    }

    /**
     * @return bool
     */
    function auth_validatelogin()
    {
        //prevent replay attack
        if (!Seminar_Session::check_ticket(Request::option('login_ticket'))) {
            return false;
        }

        $this->check_environment();

        $this->auth["uname"] = Request::get('loginname'); // This provides access for "loginform.ihtml"
        $this->auth["jscript"] = Request::get('resolution') != "";

        $check_auth = StudipAuthAbstract::CheckAuthentication(Request::get('loginname'), Request::get('password'));

        if ($check_auth['uid']) {
            $uid = $check_auth['uid'];
            if (isset($check_auth['need_email_activation']) && $check_auth['need_email_activation'] == $uid) {
                $this->need_email_activation = $uid;
                $_SESSION['semi_logged_in'] = $uid;
                return false;
            }
            $user = $check_auth['user'];
            $this->auth["perm"] = $user->perms;
            $this->auth["uname"] = $user->username;
            $this->auth["auth_plugin"] = $user->auth_plugin;
            $this->auth_set_user_settings($user);

            Metrics::increment('core.login.succeeded');

            return $uid;
        } else {
            Metrics::increment('core.login.failed');
            $this->error_msg = $check_auth['error'];
            return false;
        }
    }

    /**
     * @param $user
     */
    function auth_set_user_settings($user)
    {
        $divided = explode('x', Request::get('resolution'));
        $this->auth["xres" . ""] = !empty($divided[0]) ? (int) $divided[0] : 1024; //default
        $this->auth['yres'] = !empty($divided[1]) ? (int)$divided[1] : 768; //default
        // Change X-Resulotion on Multi-Screen Systems (as Matrox Graphic-Adapters are)
        if ($this->auth['xres'] / $this->auth['yres'] > 2) {
            $this->auth['xres'] = $this->auth['xres'] / 2;
        }
        $user = User::toObject($user);
        //restore user-specific language preference
        if ($user->preferred_language) {
            // we found a stored setting for preferred language
            $_SESSION['_language'] = $user->preferred_language;
        }
    }

    /**
     * setup dummy user environment
     */
    function check_environment()
    {
        global $_language_path;

        if (!isset($GLOBALS['user']) || $GLOBALS['user']->id !== 'nobody') {
            $GLOBALS['user'] = new Seminar_User('nobody');
            $GLOBALS['perm'] = new Seminar_Perm();
            $GLOBALS['auth'] = $this;
        }

        if (empty($_SESSION['_language'])) {
            $_SESSION['_language'] = get_accepted_languages();
        }

        // init of output via I18N
        $_language_path = init_i18n($_SESSION['_language']);
        include 'config.inc.php';

        if (!empty($_SESSION['contrast'])) {
            PageLayout::addStylesheet('accessibility.css');
        }
    }
}
