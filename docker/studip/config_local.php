<?php
/*basic settings for Stud.IP
----------------------------------------------------------------
you find here the basic system settings. You shouldn't have to touch much of them...
please note the CONFIG.INC.PHP for the indivual settings of your installation!*/

namespace Studip {
    //const ENV = 'development';
    define ('ENV', getenv('ENV') ?? 'development');
}

namespace {
    /*settings for database access
    ----------------------------------------------------------------
    please fill in your database connection settings.
    */

    // default Stud.IP database (DB_Seminar)
    $DB_STUDIP_HOST = getenv('MYSQL_HOST');
    $DB_STUDIP_USER = getenv('MYSQL_USER');
    $DB_STUDIP_PASSWORD = getenv('MYSQL_PASSWORD');
    $DB_STUDIP_DATABASE = getenv('MYSQL_DATABASE');
    $MAIL_TRANSPORT = getenv('MAIL_TRANSPORT');

    /*URL
    ----------------------------------------------------------------
    customize if automatic detection fails, e.g. when installation is hidden
    behind a proxy
    */
    //$CANONICAL_RELATIVE_PATH_STUDIP = '/';
    //$ABSOLUTE_URI_STUDIP = 'https://www.studip.de/';
    //$ASSETS_URL = 'https://www.studip.de/assets/';

    // Set proxy url
    if ($PROXY_URL = getenv('PROXY_URL')) {
        $ABSOLUTE_URI_STUDIP = $PROXY_URL;
        $ASSETS_URL = $PROXY_URL.'/assets/';
        unset($PROXY_URL);
    }

    // Use autoproxy
    if (getenv('AUTO_PROXY')) {
        $ABSOLUTE_URI_STUDIP = $_SERVER['HTTP_X_FORWARDED_PROTO'].'://'.$_SERVER['HTTP_X_FORWARDED_HOST'].'/';
        $ASSETS_URL = $ABSOLUTE_URI_STUDIP.'/assets/';
    }
    $MAIL_TRANSPORT = getenv('STUDIP_MAIL_TRANSPORT');

    $CONTENT_LANGUAGES['en_GB'] = ['picture' => 'lang_en.gif', 'name' => 'English'];


     if (getenv('BUT_FIT_IDP')) {
     #BUT FIT SSO plugin
     $STUDIP_AUTH_PLUGIN[] = "SimpleSamlPHP";

     #Settings for SimpleSamlPHP that allow use of BUT FIT IDP
     $STUDIP_AUTH_CONFIG_SIMPLESAMLPHP = array(
            "return_to_url" => 'https://studip.ceskar.xyz/index.php?sso=simplesamlphp&cancel_login=1',
            "sp_name" => 'default-sp',
            "username_attribute" => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.13',
            "user_data_mapping" =>      array(  "auth_user_md5.perms" => array("callback" => "assignButFitRoles", "map_args" => ""),
                                                "auth_user_md5.Email" => array("callback" => "getButFitUserData", "map_args" => "urn:oid:0.9.2342.19200300.100.1.3"),
                                                "auth_user_md5.Nachname" => array("callback" => "getButFitUserData", "map_args" => "urn:oid:2.5.4.4"),
                                                "auth_user_md5.Vorname" => array("callback" => "getButFitUserData", "map_args" => "urn:oid:2.5.4.42")));
     }

}
