<?php
final class AddReportBarrierModeConfiguration extends Migration
{
    public function description()
    {
        return 'Adds the configuration option REPORT_BARRIER_MODE';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'REPORT_BARRIER_MODE', 'on', 'string', 'global',
                    'accessibility', 'Einstellungen zum Formular zu Melden einer Barriere (\"on\" = immer an, \"logged-in\" = nur für angemeldete Personen, \"off\" = ausgeschaltet)',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE config, config_values
                  FROM `config`
                  LEFT JOIN `config_values` USING(`field`)
                  WHERE `field` = 'REPORT_BARRIER_MODE'";
        DBManager::get()->exec($query);
    }
}
