<?php

class AddActionMenuThreshold extends Migration
{
    public function description()
    {
        return 'add option for when to render action menu as separate icons';
    }

    public function up()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'        => 'ACTION_MENU_THRESHOLD',
            'description' => 'Obergrenze an Einträgen, bis zu der ein Aktionsmenü als Icons dargestellt wird',
            'section'     => 'global',
            'type'        => 'integer',
            'value'       => '1'
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'ACTION_MENU_THRESHOLD'";
        DBManager::get()->exec($query);
    }
}
