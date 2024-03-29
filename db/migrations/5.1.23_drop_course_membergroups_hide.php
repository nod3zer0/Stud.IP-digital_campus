<?php

class DropCourseMembergroupsHide extends Migration
{
    public function description()
    {
        return 'Drop config option to hide course member groups page';
    }

    public function up()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'COURSE_MEMBERGROUPS_HIDE'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'COURSE_MEMBERGROUPS_HIDE',
            ':description' => 'Über diese Option können Sie die Teilnehmendengruppenliste für Studierende der Veranstaltung unsichtbar machen',
            ':range'       => 'course',
            ':type'        => 'boolean',
            ':value'       => '0'
        ]);
    }
}
