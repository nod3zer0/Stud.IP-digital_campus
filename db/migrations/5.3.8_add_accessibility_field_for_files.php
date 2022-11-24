<?php


class AddAccessibilityFieldForFiles extends Migration
{
    public function description()
    {
        return 'Add field is_accessible to file table; creates config for accessibility info text';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `files`
            ADD `is_accessible` TINYINT(1) NULL AFTER `author_name`"
        );


        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `section`, `range`, `description`, `mkdate`, `chdate`)
                  VALUES (:name, :value, :type, :section, :range, :description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'        => 'ACCESSIBILITY_INFO_TEXT',
            'value'       => '',
            'type'        => 'i18n',
            'section'     => 'accessibility',
            'range' => 'global',
            'description' => 'Diese Konfiguration bitte unter Admin -> Standort -> Infotext zu barrierefreien Dateien anpassen!'
        ]);

    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `files` DROP `is_accessible`"
        );

        $query = "DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'ACCESSIBILITY_INFO_TEXT'";
        DBManager::get()->exec($query);
    }
}
