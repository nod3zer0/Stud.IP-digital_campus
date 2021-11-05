<?php

class RemoveAccessKeyEnable extends Migration
{
    public function description()
    {
        return 'Removes ACCESSKEY_ENABLE';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM `config_values` WHERE `field` = 'ACCESSKEY_ENABLE'");
        $db->exec("DELETE FROM `config` WHERE `field` = 'ACCESSKEY_ENABLE'");
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec(
            "INSERT INTO `config`
            (`field`, `type`, `value`, `range`, `description`)
            VALUES
            ('ACCESSKEY_ENABLE', 'boolean', '', 'user', ' Schaltet die Nutzung von Shortcuts für einen User ein oder aus, Systemdefault')"
        );
    }
}
