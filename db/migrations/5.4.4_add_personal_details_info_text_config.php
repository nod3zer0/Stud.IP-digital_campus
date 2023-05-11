<?php

class AddPersonalDetailsInfoTextConfig extends Migration
{
    public function description()
    {
        return 'Adds the configuration PERSONAL_DETAILS_INFO_TEXT, if it doesn\'t exist yet. Also adds english translation.';
    }

    protected function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
             VALUES
             (
                 'PERSONAL_DETAILS_INFO_TEXT', 'Einige Ihrer persönlichen Daten werden nicht in Stud.IP verwaltet und können daher hier nicht geändert werden.',
                 'i18n', 'global', 'global', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                 'Der Infotext der unter Profil->Persönliche Angaben->Grunddaten angezeigt wird, wenn man nicht die Standard-Auth nutzt.'
             )"
        );

        $db->execute(
            "INSERT IGNORE INTO `i18n`
            (`object_id`, `table`, `field`, `lang`, `value`)
            VALUES
            (
                MD5('PERSONAL_DETAILS_INFO_TEXT'), 'config', 'value', 'en_GB',
                'Some of your personal data is not managed in Stud.IP and therefore cannot be changed here.'
            )"
        );
    }

    protected function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM `config_values` WHERE `field` = 'PERSONAL_DETAILS_INFO_TEXT'");
        $db->exec("DELETE FROM `config` WHERE `field` = 'PERSONAL_DETAILS_INFO_TEXT'");
        $db->exec("DELETE FROM `i18n` WHERE `object_id` = MD5('PERSONAL_DETAILS_INFO_TEXT') AND `table` = 'config' AND `field` = 'value'");
    }
}
