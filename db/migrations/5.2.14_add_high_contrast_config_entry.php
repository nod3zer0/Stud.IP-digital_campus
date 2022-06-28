<?php


class AddHighContrastConfigEntry extends Migration
{
    public function description()
    {
        return 'Adds configuration field USER_HIGH_CONTRAST';
    }


    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('USER_HIGH_CONTRAST', '0', 'boolean', 'user',
            'accessibility', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Schaltet ein barrierefreies Stylesheet mit hohem Kontrast ein oder aus.')"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM `config_values`
            WHERE `field` = 'USER_HIGH_CONTRAST'"
        );
        $db->exec(
            "DELETE FROM `config`
            WHERE `field` = 'USER_HIGH_CONTRAST'"
        );
    }
}
