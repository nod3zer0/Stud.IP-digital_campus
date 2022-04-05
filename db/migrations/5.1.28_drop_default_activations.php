<?php

class DropDefaultActivations extends Migration
{
    public function description()
    {
        return 'Drop unused plugin default activations';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('DROP TABLE IF EXISTS plugins_default_activations');
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec("
            CREATE TABLE plugins_default_activations (
              pluginid int(10) unsigned NOT NULL DEFAULT 0,
              institutid char(32) COLLATE latin1_bin NOT NULL DEFAULT '',
              PRIMARY KEY (pluginid, institutid)
            )
        ");
    }
}
