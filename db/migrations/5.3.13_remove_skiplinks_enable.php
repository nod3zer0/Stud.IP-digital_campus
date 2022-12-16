<?php


class RemoveSkiplinksEnable extends Migration
{
    public function description()
    {
        return 'Removes SKIPLINKS_ENABLE.';
    }

    protected function up()
    {
        $db = DBManager::get();
        $db->exec("DELETE FROM `config_values` WHERE `field` = 'SKIPLINKS_ENABLE'");
        $db->exec("DELETE FROM `config` WHERE `field` = 'SKIPLINKS_ENABLE'");
    }

    protected function down()
    {
        $db = DBManager::get();
        $db->exec(
            "INSERT INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES
            ('SKIPLINKS_ENABLE', '', 'boolean', 'user', 'privacy', 1311411856, 1311411856, 'Wählen Sie diese Option, um Skiplinks beim ersten Drücken der Tab-Taste anzuzeigen (Systemdefault).')"
        );
    }
}
