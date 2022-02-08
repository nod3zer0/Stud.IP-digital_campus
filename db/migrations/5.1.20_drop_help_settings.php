<?php

class DropHelpSettings extends Migration
{
    public function description()
    {
        return 'Drop unused help system settings';
    }

    public function up()
    {
        $settings = [
            'EXTERNAL_HELP',
            'EXTERNAL_HELP_LOCATIONID',
            'EXTERNAL_HELP_URL',
            'HELP_CONTENT_CURRENT_VERSION'
        ];
        // remove config entries
        $query = 'DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (?)';
        DBManager::get()->execute($query, [$settings]);
    }

    public function down()
    {
        // create config entries
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'EXTERNAL_HELP',
            ':description' => 'Schaltet das externe Hilfesystem ein',
            ':section'     => '',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);
        $statement->execute([
            ':name'        => 'EXTERNAL_HELP_LOCATIONID',
            ':description' => 'Eine eindeutige ID zur Identifikation der gewünschten Hilfeseiten, leer bedeutet Standardhilfe',
            ':section'     => '',
            ':range'       => 'global',
            ':type'        => 'string',
            ':value'       => 'default'
        ]);
        $statement->execute([
            ':name'        => 'EXTERNAL_HELP_URL',
            ':description' => 'URL Template für das externe Hilfesystem',
            ':section'     => '',
            ':range'       => 'global',
            ':type'        => 'string',
            ':value'       => 'https://hilfe.studip.de/index.php/%s'
        ]);
        $statement->execute([
            ':name'        => 'HELP_CONTENT_CURRENT_VERSION',
            ':description' => 'Aktuelle Version der Helpbar-Einträge in Stud.IP',
            ':section'     => 'global',
            ':range'       => 'global',
            ':type'        => 'string',
            ':value'       => '3.1'
        ]);
    }
}
