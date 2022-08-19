<?php


class AddShowFolderSizeConfig extends Migration
{
    public function description()
    {
        return 'Adds the configuration SHOW_FOLDER_SIZE.';
    }


    protected function up()
    {
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`, `mkdate`, `chdate`,
            `description`)
            VALUES
            ('SHOW_FOLDER_SIZE', '1', 'boolean', 'global',
            'files', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'SHOW_FOLDER_SIZE gibt an, ob die Anzahl der Objekte (Dateien und Unterordner) in einem Ordner angezeigt werden sollen.')"
        );
    }

    protected function down()
    {
        DBManager::get()->exec(
            "DELETE FROM `config_values` WHERE `field` = 'SHOW_FOLDER_SIZE'"
        );
    }
}
