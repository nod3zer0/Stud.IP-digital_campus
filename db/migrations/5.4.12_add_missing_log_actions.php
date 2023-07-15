<?php

final class AddMissingLogActions extends Migration
{
    public function description()
    {
        return 'add missing log-actions';
    }

    public function up()
    {
        DBManager::get()->exec("
            INSERT IGNORE INTO `log_actions`
            SET `action_id` = MD5('USER_LOCK'),
                `name` = 'USER_LOCK',
                `description` = 'Nutzer wird gesperrt',
                `info_template` = '%user sperrt %user(%affected) (%info)',
                `active` = '1',
                `expires` = '0'
        ");

        DBManager::get()->exec("
            INSERT IGNORE INTO `log_actions`
            SET `action_id` = MD5('FILE_DELETE'),
                `name` = 'FILE_DELETE',
                `description` = 'Nutzer löscht Datei',
                `info_template` = '%user löscht Datei %info (File-Id: %affected)',
                `active` = '1',
                `expires` = '0'
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO `log_actions`
            SET `action_id` = MD5('FOLDER_DELETE'),
                `name` = 'FOLDER_DELETE',
                `description` = 'Nutzer löscht Ordner',
                `info_template` = '%user löscht Datei %info (Folder-Id: %affected)',
                `active` = '1',
                `expires` = '0'
        ");
    }

    public function down()
    {
        $actions = ['USER_LOCK', 'FILE_DELETE', 'FOLDER_DELETE'];

        DBManager::get()->execute(
            "DELETE `log_events` FROM `log_events` JOIN `log_actions` USING (`action_id`) WHERE `name` IN (?)",
            [$actions]
        );
        DBManager::get()->execute("DELETE FROM `log_actions` WHERE `name` IN (?)", [$actions]);
    }
}
