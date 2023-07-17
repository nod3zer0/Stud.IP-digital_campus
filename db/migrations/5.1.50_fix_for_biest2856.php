<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2856
 */
final class FixForBiest2856 extends Migration
{
    public function description()
    {
        return 'Removes the obsolete configuration RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION';
    }

    protected function up()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES ('RESOURCES_ALLOW_VIEW_RESOURCE_OCCUPATION', '1', 'boolean', 'global', 'resources', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Dürfen alle Nutzer Ressourcenbelegungen einsehen?')";
        DBManager::get()->exec($query);
    }
}


