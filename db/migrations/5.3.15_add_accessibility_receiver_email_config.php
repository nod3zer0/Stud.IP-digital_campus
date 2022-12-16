<?php

class AddAccessibilityReceiverEmailConfig extends Migration
{
    public function description()
    {
        return 'Adds the configuration ACCESSIBILITY_RECEIVER_EMAIL, if it doesn\'t exist yet.';
    }

    protected function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'ACCESSIBILITY_RECEIVER_EMAIL', 'array', 'global', '', 'accessibility',
                 'Die E-Mail-Adressen der Personen, die beim Melden einer Barriere benachrichtigt werden sollen.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
    }

    protected function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM `config_values` WHERE `field` = 'ACCESSIBILITY_RECEIVER_EMAIL'");
        $db->exec("DELETE FROM `config` WHERE `field` = 'ACCESSIBILITY_RECEIVER_EMAIL'");
    }
}
