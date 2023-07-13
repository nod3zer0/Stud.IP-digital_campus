<?php
final class ContentmodulesDescription extends Migration
{
    public function description()
    {
        return 'Content modules of a course, institute or studygroup got revamped.';
    }

    protected function up()
    {
        $query = "ALTER TABLE `plugins`
                  ADD COLUMN `description` TEXT DEFAULT NULL,
                  ADD COLUMN `description_mode` ENUM('add', 'override_description', 'replace_all') DEFAULT 'add',
                  ADD COLUMN `highlight_until` INT(11) UNSIGNED DEFAULT NULL,
                  ADD COLUMN `highlight_text` VARCHAR(64) DEFAULT NULL,
                  ADD KEY `highlight_until` (`highlight_until`)";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'CONTENTMODULES_TILED_DISPLAY',
            ':description' => 'Bevorzugt ein Nutzer eine Kachelansicht auf der Werkzeugseite in den Veranstaltungen oder lieber eine Tabelle?',
            ':range'       => 'user',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);
    }

    protected function down()
    {
        $query = "ALTER TABLE `plugins`
                  DROP COLUMN `description`,
                  DROP COLUMN `highlight_until`,
                  DROP COLUMN `highlight_text`";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `config_values`
                  WHERE `field` = 'CONTENTMODULES_TILED_DISPLAY' ";
        DBManager::get()->exec($query);
        $query = "DELETE FROM `config`
                  WHERE `field` = 'CONTENTMODULES_TILED_DISPLAY' ";
        DBManager::get()->exec($query);
    }
}
