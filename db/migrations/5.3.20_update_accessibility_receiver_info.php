<?php

class UpdateAccessibilityReceiverInfo extends Migration
{
    public function description()
    {
        return 'Update info text for ACCESSIBILITY_RECEIVER_EMAIL to clarify how to use it.';
    }

    protected function up()
    {
        $db = DBManager::get();

        $db->exec(
            "UPDATE `config`
                SET `description` = 'Die E-Mail-Adressen der Personen, die beim Melden einer Barriere benachrichtigt werden sollen.
                Beispiel: [\"mailadresse1@server.de\",\"mailadresse2@server.de\"]'
                WHERE `field` = 'ACCESSIBILITY_RECEIVER_EMAIL'
            "
        );
    }

    protected function down()
    {
        $db = DBManager::get();
        $db->exec(
            "UPDATE `config`
                SET `description` = 'Die E-Mail-Adressen der Personen, die beim Melden einer Barriere benachrichtigt werden sollen.'
                WHERE `field` = 'ACCESSIBILITY_RECEIVER_EMAIL'
            "
        );
    }
}
