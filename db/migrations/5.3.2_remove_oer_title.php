<?php


class RemoveOerTitle extends Migration
{
    public function description()
    {
        return 'Removes the option OER_TITLE from the config';
    }


    public function up()
    {
        DBManager::get()->exec(
            "DELETE FROM `config_values`
            WHERE `field` = 'OER_TITLE'"
        );
        DBManager::get()->exec(
            "DELETE FROM `config`
            WHERE `field` = 'OER_TITLE'"
        );
    }


    public function down()
    {
        $query = "INSERT INTO `config`
                  SET `field` = :field,
                      `value` = :value,
                      `type` = :type,
                      `range` = :range,
                      `section` = :section,
                      `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `description` = :description";
        $config_statement = DBManager::get()->prepare($query);
        $config_statement->execute([
            ':field'       => 'OER_TITLE',
            ':value'       => 'OER Campus',
            ':type'        => 'string',
            ':range'       => 'global',
            ':section'     => 'OERCampus',
            ':description' => 'Name des OER Campus in Stud.IP',
        ]);
    }
}
