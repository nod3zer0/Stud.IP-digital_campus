<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/1985
 */
final class RemoveResourcesEnableExpertScheduleViewConfiguration extends Migration
{
    public function description()
    {
        return 'Removes the configuration RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW '
             . 'as well as RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST';
    }

    protected function up()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW',
                      'RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST'
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES ('RESOURCES_ENABLE_EXPERT_SCHEDULE_VIEW', '0', 'boolean', 'global', 'resources', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Enables the expert view of the course schedules')";
        DBManager::get()->exec($query);

        $query = "INSERT INTO `config` (`field`, `value`,`type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES ('RESOURCES_ALLOW_SEMASSI_SKIP_REQUEST', '1', 'boolean', 'global', 'resources', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Schaltet das Pflicht, eine Raumanfrage beim Anlegen einer Veranstaltung machen zu müssen, ein oder aus')";
        DBManager::get()->exec($query);
    }
}
