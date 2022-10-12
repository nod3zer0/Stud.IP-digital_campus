<?php
final class RemoveAllowTiledDisplayConfiguration extends Migration
{
    protected function up()
    {
        $query = "DELETE config, config_values
                  FROM `config`
                  LEFT JOIN `config_values` USING(`field`)
                  WHERE `field` = 'MY_COURSES_ALLOW_TILED_DISPLAY'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_ALLOW_TILED_DISPLAY', '0', 'boolean', 'global',
                    'MeineVeranstaltungen', 'Soll die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert werden?',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);
    }
}
