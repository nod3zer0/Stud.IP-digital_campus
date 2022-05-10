<?php

class DropCronjobsEscalation extends Migration
{
    public function description()
    {
        return 'Drop CRONJOBS_ESCALATION system setting';
    }

    public function up()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'CRONJOBS_ESCALATION'";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'CRONJOBS_ESCALATION',
            ':description' => 'Gibt an, nach wievielen Sekunden ein Cronjob als steckengeblieben angesehen wird',
            ':section'     => 'global',
            ':type'        => 'integer',
            ':value'       => '300'
        ]);
    }
}
