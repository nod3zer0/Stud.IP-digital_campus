<?php

final class Tic1583CourseSelector extends Migration
{
    public function description()
    {
        return 'adds the sorting option for the quick course selection';
    }

    public function up()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'        => 'COURSE_MANAGEMENT_SELECTOR_ORDER_BY',
            'description' => 'Gibt an, nach welchem Kriterium die Veranstaltungsschnellwauswahl innerhalb der Veranstaltungsverwaltung sortiert werden soll',
            'range'       => 'user',
            'type'        => 'string',
            'value'       => 'name'
        ]);
    }

    public function down()
    {
        DBManager::get()->exec("DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'COURSE_MANAGEMENT_SELECTOR_ORDER_BY'");
    }

}
