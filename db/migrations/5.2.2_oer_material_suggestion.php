<?php
class OerMaterialSuggestion extends Migration
{
    public function description()
    {
        return "Adds config option to enable suggestions";
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config`
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
            ':field'       => 'OER_ENABLE_SUGGESTIONS',
            ':value'       => '1',
            ':type'        => 'boolean',
            ':range'       => 'global',
            ':section'     => 'OERCampus',
            ':description' => 'Studierendenvorschläge erlauben?',
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'OER_ENABLE_SUGGESTIONS'";
        DBManager::get()->exec($query);
    }

}
