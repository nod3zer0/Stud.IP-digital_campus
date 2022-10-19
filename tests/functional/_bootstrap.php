<?php

// set include path
$inc_path = ini_get('include_path');
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../..';
$inc_path .= PATH_SEPARATOR . dirname(__FILE__) . '/../../config';
ini_set('include_path', $inc_path);

require 'lib/classes/StudipAutoloader.php';
require 'lib/functions.php';
require_once 'lib/language.inc.php';
require 'lib/visual.inc.php';

$STUDIP_BASE_PATH = realpath(dirname(__FILE__) . '/../..');

StudipAutoloader::register();
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/calendar', 'Studip\\Calendar');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/classes');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/classes', 'Studip');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/exceptions');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/exceptions/resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/filesystem');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/migrations');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/models');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/models/resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/phplib');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/raumzeit');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/resources');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/plugins/core');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/modules');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/plugins/db');
StudipAutoloader::addAutoloadPath($STUDIP_BASE_PATH . '/lib/plugins/engine');

// load config-variables
$added_configs = [];
StudipFileloader::load(
    'config_defaults.inc.php config_local.inc.php',
    $added_configs,
    compact('STUDIP_BASE_PATH', 'ABSOLUTE_URI_STUDIP', 'ASSETS_URL', 'CANONICAL_RELATIVE_PATH_STUDIP'),
    true
);
foreach($added_configs as $key => $value) {
    $GLOBALS[$key] = $value;
}
require 'config/config.inc.php';

// Do not send mails of any kind during tests
require 'vendor/email_message/email_message.php';
require 'vendor/email_message/debug_message.php';
StudipMail::setDefaultTransporter(new debug_message_class());

require_once 'vendor/flexi/lib/flexi.php';
$GLOBALS['template_factory'] = new Flexi_TemplateFactory(dirname(dirname(__DIR__)) . '/templates');

// Disable caching to fallback to memory cache
$GLOBALS['CACHING_ENABLE'] = false;

// SimpleORMapFake
if (!class_exists('StudipTestHelper')) {
    class StudipTestHelper
    {
        static function set_up_tables($tables)
        {
            $cache = StudipCacheFactory::getCache(false);

            // second step, expire table scheme
            SimpleORMap::expireTableScheme();

            $schemes = [];

            foreach ($tables as $db_table) {
                include TEST_FIXTURES_PATH."simpleormap/$db_table.php";
                $db_fields = $pk = [];
                foreach ($result as $rs) {
                    $db_fields[mb_strtolower($rs['name'])] = [
                        'name'    => $rs['name'],
                        'null'    => $rs['null'],
                        'default' => $rs['default'],
                        'type'    => $rs['type'],
                        'extra'   => $rs['extra']
                    ];
                    if ($rs['key'] == 'PRI'){
                        $pk[] = mb_strtolower($rs['name']);
                    }
                }
                $schemes[$db_table]['db_fields'] = $db_fields;
                $schemes[$db_table]['pk'] = $pk;
            }

            $cache->write('DB_TABLE_SCHEMES', serialize($schemes));
        }

        static function tear_down_tables()
        {
            SimpleORMap::expireTableScheme();
        }
    }
}
