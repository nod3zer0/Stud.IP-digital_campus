<?php
# Lifter002: TODO
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
* main-class for connected systems
*
* This class contains the main methods of the elearning-interface to connect content-management-systems.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  elearning_interface_modules
* @module       ConnectedCMS
* @package  ELearning-Interface
*/
class ConnectedCMS
{
    public $title;

    public $is_active;
    public $cms_type;
    public $name = null;
    public $ABSOLUTE_PATH_ELEARNINGMODULES = null;
    public $ABSOLUTE_PATH_SOAP = null;
    public $RELATIVE_PATH_DB_CLASSES = false;
    public $CLASS_PREFIX = null;
    public $auth_necessary = null;
    public $USER_AUTO_CREATE = null;
    public $USER_PREFIX = null;
    public $target_file = null;
    public $logo_file = null;
    public $db_classes;
    public $soap_data = null;
    public $soap_client;
    public $types = null;
    public $roles = null;

    public $db;
    public $db_class;
    public $link;
    public $user;
    public $permissions;
    public $content_module;

    /**
    * constructor
    *
    * init class. don't call directly but by extending class ("new Ilias3ConnectedCMS($cms)" for example), except for basic administration
    * @access
    * @param string $cms system-type
    */
    public function __construct($cms = "")
    {
        $this->cms_type = $cms;
        $this->is_active = (bool) Config::get()->getValue("ELEARNING_INTERFACE_{$cms}_ACTIVE");

        if ($cms) {
            $this->init($cms);
        }
    }

    /**
    * init settings
    *
    * gets settings from config-array and initializes db
    * @access private
    * @param string $cms system-type
    */
    public function init($cms)
    {
        global $ELEARNING_INTERFACE_MODULES;

        $this->name = $ELEARNING_INTERFACE_MODULES[$cms]['name'] ?? null;
        $this->ABSOLUTE_PATH_ELEARNINGMODULES = $ELEARNING_INTERFACE_MODULES[$cms]["ABSOLUTE_PATH_ELEARNINGMODULES"];
        $this->ABSOLUTE_PATH_SOAP = $ELEARNING_INTERFACE_MODULES[$cms]["ABSOLUTE_PATH_SOAP"];
        if (isset($ELEARNING_INTERFACE_MODULES[$cms]["RELATIVE_PATH_DB_CLASSES"])) {
            $this->RELATIVE_PATH_DB_CLASSES = $ELEARNING_INTERFACE_MODULES[$cms]["RELATIVE_PATH_DB_CLASSES"];
            $this->db_classes = $ELEARNING_INTERFACE_MODULES[$cms]["db_classes"];
        } else {
            $this->RELATIVE_PATH_DB_CLASSES = false;
        }
        $this->CLASS_PREFIX = $ELEARNING_INTERFACE_MODULES[$cms]['CLASS_PREFIX'];
        $this->auth_necessary = $ELEARNING_INTERFACE_MODULES[$cms]['auth_necessary'];
        $this->USER_AUTO_CREATE = $ELEARNING_INTERFACE_MODULES[$cms]['USER_AUTO_CREATE'] ?? null;
        $this->USER_PREFIX = $ELEARNING_INTERFACE_MODULES[$cms]['USER_PREFIX'] ?? null;
        $this->target_file = $ELEARNING_INTERFACE_MODULES[$cms]['target_file'] ?? null;
        $this->logo_file = $ELEARNING_INTERFACE_MODULES[$cms]['logo_file'] ?? null;
        $this->soap_data = $ELEARNING_INTERFACE_MODULES[$cms]['soap_data'] ?? null;
        $this->types = $ELEARNING_INTERFACE_MODULES[$cms]['types'] ?? null;
        $this->roles = $ELEARNING_INTERFACE_MODULES[$cms]['roles'] ?? null;
    }

    /**
    * init subclasses
    *
    * loads classes for user-functions
    * @access public
    */
    public function initSubclasses()
    {
        if ($this->auth_necessary) {
            require_once $this->CLASS_PREFIX . "ConnectedUser.class.php";
            $classname = $this->CLASS_PREFIX . "ConnectedUser";
            $this->user = new $classname($this->cms_type);

            require_once $this->CLASS_PREFIX  . "ConnectedPermissions.class.php";
            $classname = $this->CLASS_PREFIX  . "ConnectedPermissions";
            $this->permissions = new $classname($this->cms_type);
        }
        require_once $this->CLASS_PREFIX . "ConnectedLink.class.php";
        $classname = $this->CLASS_PREFIX . "ConnectedLink";
        $this->link = new $classname($this->cms_type);
    }

    /**
    * get connection status
    *
    * checks settings
    * @access public
    * @param string $cms system-type
    * @return array messages
    */
    public function getConnectionStatus($cms = "")
    {
        $msg = [
            'path' => [],
        ];

        if ($this->cms_type == "") {
            $this->init($cms);
        }

        // check connection to CMS
        if (!$this->auth_necessary) {
            $msg['auth'] = [
                'info' => _('Eine Authentifizierung ist für dieses System nicht vorgesehen.')
            ];
        }

        // check for SOAP-Interface
        if (in_array($this->CLASS_PREFIX, ['Ilias3','Ilias4','Ilias5'])) {
            $ch = curl_init($this->ABSOLUTE_PATH_ELEARNINGMODULES . 'login.php');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);

            if (curl_getinfo($ch, CURLINFO_RESPONSE_CODE) !== 200) {
                $msg['path']['error'] = sprintf(
                    _('Die Verbindung zum System "%s" konnte nicht hergestellt werden. Der Pfad "%s" ist ungültig.'),
                    $this->name,
                    $this->ABSOLUTE_PATH_ELEARNINGMODULES
                );

            } else {
                $msg['path']['info'] = sprintf(
                    _('Die %s-Installation wurde gefunden.'),
                    $this->name
                );
            }

            $msg['soap'] = [];
            if (!Config::get()->SOAP_ENABLE) {
                $msg['soap']['error'] = _('Das Stud.IP-Modul für die SOAP-Schnittstelle ist nicht aktiviert. Ändern Sie den entsprechenden Eintrag in der Konfigurationsdatei "local.inc".');
            } elseif (!is_array($this->soap_data)) {
                $msg['soap']['error'] = _('Die SOAP-Verbindungsdaten sind für dieses System nicht gesetzt. Ergänzen Sie die Einstellungen für dieses Systems um den Eintrag "soap_data" in der Konfigurationsdatei "local.inc".');
            } else {
                $this->soap_client = new StudipSoapClient($this->ABSOLUTE_PATH_SOAP);
                $msg['soap']['info'] = _('Das SOAP-Modul ist aktiv.');
            }
        } else {
            $file = fopen($this->ABSOLUTE_PATH_ELEARNINGMODULES, 'r');
            if ($file === false) {
                $msg['path']['error'] = sprintf(
                    _('Die Verbindung zum System "%s" konnte nicht hergestellt werden. Der Pfad "%s" ist ungültig.'),
                    $this->name,
                    $this->ABSOLUTE_PATH_ELEARNINGMODULES
                );
            } else {
                fclose($file);
                $msg['path']['info'] = sprintf(
                    _("Die %s-Installation wurde gefunden."),
                    $this->name
                );

                // check if target-file exists
                $msg['auth'] = [];

                $file = fopen($this->ABSOLUTE_PATH_ELEARNINGMODULES . $this->target_file, 'r');
                if ($file === false) {
                    $msg['auth']['error'] = sprintf(
                        _('Die Zieldatei "%s" liegt nicht im Hauptverzeichnis der %s-Installation.'),
                        $this->target_file,
                        $this->name
                    );
                } else {
                    fclose($file);
                    $msg['auth']['info'] = _('Die Zieldatei ist vorhanden.');
                }
            }
        }

        $el_path = $GLOBALS['STUDIP_BASE_PATH'] . '/lib/elearning';
        // check if needed classes exist
        $files = [
            'class_link'    => "{$el_path}/{$this->CLASS_PREFIX}ConnectedLink.class.php",
            'class_content' => "{$el_path}/{$this->CLASS_PREFIX}ContentModule.class.php",
            'class_cms'     => "{$el_path}/{$this->CLASS_PREFIX}ConnectedCMS.class.php",
        ];

        if ($this->auth_necessary) {
            $files['class_user'] = "{$el_path}/{$this->CLASS_PREFIX}ConnectedUser.class.php";
            $files['class_perm'] = "{$el_path}/{$this->CLASS_PREFIX}ConnectedPermissions.class.php";
        }

        $errors = 0;
        foreach ($files as $index => $file) {
            if (!file_exists($file)) {
                $msg[$index] = [
                    'error' => sprintf(_('Die Datei "%s" existiert nicht.'), $file),
                ];
                $errors += 1;
            }
        }

        $msg['classes'] = [];
        if ($errors === 0) {
            require_once $files['class_cms'];
            $msg['classes']['info'] = sprintf(
                _('Die Klassen der Schnittstelle zum System "%s" wurden geladen.'),
                $this->name
            );
        } else {
            $msg['classes']['error'] = sprintf(
                _('Die Klassen der Schnittstelle zum System "%s" wurden nicht geladen.'),
                $this->name
            );
        }

        return $msg;
    }

    /**
    * get preferences
    *
    * shows additional settings. can be overwritten by subclass.
    * @access public
    */
    public function getPreferences()
    {
        if ($this->types != "")
        {
            echo "<b>" . _("Angebundene Lernmodul-Typen: ") . "</b>";
            echo "<br>\n";
            foreach($this->types as $key => $type)
                echo Icon::create($type["icon"], Icon::ROLE_INACTIVE)->asImg() . $type["name"] . " ($key)<br>\n";
            echo "<br>\n";
        }

        if ($this->db_classes != "")
        {
            echo "<b>" . _("Verwendete DB-Zugriffs-Klassen: ") . "</b>";
            echo "<br>\n";
            foreach($this->db_classes as $key => $type) {
                echo $type["file"] . " ($key)<br>\n";
            }
            echo "<br>\n";
        }
    }

    /**
    * create new instance of subclass content-module with given values
    *
    * creates new instance of subclass content-module with given values
    * @access public
    * @param array $data module-data
    * @param boolean $is_connected is module connected to seminar?
    */
    public function setContentModule($data, $is_connected = false)
    {
        global $current_module;
        $current_module = $data["ref_id"];

        require_once($this->CLASS_PREFIX . "ContentModule.class.php");
        $classname = $this->CLASS_PREFIX  . "ContentModule";

        $this->content_module[$current_module] = new  $classname("", $data["type"], $this->cms_type);
        $this->content_module[$current_module]->setId($data["ref_id"]);
        $this->content_module[$current_module]->setTitle($data["title"]);
        $this->content_module[$current_module]->setDescription($data["description"]);
        $this->content_module[$current_module]->setConnectionType($is_connected);
    }

    /**
    * create new instance of subclass content-module
    *
    * creates new instance of subclass content-module
    * @access public
    * @param string $module_id module-id
    * @param string $module_type module-type
    * @param boolean $is_connected is module connected to seminar?
    */
    public function newContentModule($module_id, $module_type, $is_connected = false)
    {
        global $current_module;
        $current_module = $module_id;

        require_once($this->CLASS_PREFIX . "ContentModule.class.php");
        $classname = $this->CLASS_PREFIX  . "ContentModule";

        if ($is_connected == false)
        {
            $this->content_module[$module_id] = new  $classname("", $module_type, $this->cms_type);
            $this->content_module[$module_id]->setId($module_id);
        }
        else
        {
            $this->content_module[$module_id] = new  $classname($module_id, $module_type, $this->cms_type);
        }

        $this->content_module[$module_id]->setConnectionType($is_connected);
    }

    /**
    * get name of cms
    *
    * returns name of cms
    * @access public
    * @return string name
    */
    public function getName()
    {
        return $this->name;
    }

    /**
    * get type of cms
    *
    * returns type of cms
    * @access public
    * @return string type
    */
    public function getCMSType()
    {
        return $this->cms_type;
    }

    /**
    * get path of cms
    *
    * returns path of cms
    * @access public
    * @return string path
    */
    public function getAbsolutePath()
    {
        return $this->ABSOLUTE_PATH_ELEARNINGMODULES;
    }

    /**
    * get target file of cms
    *
    * returns target file of cms
    * @access public
    * @return string target file
    */
    public function getTargetFile()
    {
        return $this->target_file;
    }

    /**
    * get class prefix
    *
    * returns class prefix
    * @access public
    * @return string class prefix
    */
    public function getClassPrefix()
    {
        return $this->CLASS_PREFIX;
    }

    /**
    * get authentification-setting
    *
    * returns true, if authentification is necessary
    * @access public
    * @return boolean authentification-setting
    */
    public function isAuthNecessary()
    {
        return $this->auth_necessary;
    }

    /**
    * get active-setting
    *
    * returns true, if cms is active
    * @access public
    * @return boolean active-setting
    function isActive($cms = "")
    {
        return $this->is_active;
    }
    */

    /**
    * get user prefix
    *
    * returns user prefix
    * @access public
    * @return string user prefix
    */
    public function getUserPrefix()
    {
        return $this->USER_PREFIX;
    }

    /**
    * get logo-image
    *
    * returns logo-image
    * @access public
    * @return string logo-image
    */
    public function getLogo()
    {
        return "<img src=\"" . $this->logo_file . "\">";
    }

    /**
    * get user modules
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @return boolean returns false
    */
    public function getUserContentModules()
    {
        return false;
    }

    /**
    * search modules
    *
    * dummy-method. returns false. must be overwritten by subclass.
    * @access public
    * @return boolean returns false
    */
    public function searchContentModules($key)
    {
        return false;
    }

    /**
    * dummy-method. can be overwritten by subclass.
    */
    public function terminate()
    {
    }

    public function deleteConnectedModules($object_id){
        return ObjectConnections::DeleteAllConnections($object_id, $this->cms_type);
    }
}
