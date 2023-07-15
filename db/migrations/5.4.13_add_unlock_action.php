<?php

final class AddUnlockAction extends Migration
{
    public function description()
    {
        return 'add an unlock action';
    }

    public function up()
    {
        DBManager::get()->exec("
            INSERT IGNORE INTO `log_actions`
            SET `action_id` = MD5('USER_UNLOCK'),
                `name` = 'USER_UNLOCK',
                `description` = 'Nutzer wird entsperrt',
                `info_template` = '%user entsperrt %user(%affected) (%info)',
                `active` = '1',
                `expires` = '0'
        ");
    }

    public function down()
    {
        $actions = ['USER_UNLOCK'];

        DBManager::get()->execute(
            "DELETE `log_events` FROM `log_events` JOIN `log_actions` USING (`action_id`) WHERE `name` IN (?)",
            [$actions]
        );
        DBManager::get()->execute("DELETE FROM `log_actions` WHERE `name` IN (?)", [$actions]);
    }
}
