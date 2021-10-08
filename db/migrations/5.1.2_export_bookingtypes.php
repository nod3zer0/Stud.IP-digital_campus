<?php

class ExportBookingTypes extends Migration
{

    public function description()
    {
        return 'Adds a config entry for specifying which booking types shall be exported per default';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`
                  ) VALUES (
                    'RESOURCES_EXPORT_BOOKINGTYPES_DEFAULT', '[0,1,2]', 'array', 'global', 'resources',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Standardmäßig zu exportierende Belegungstypen'
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE FROM `config`
                  WHERE `field` = 'RESOURCES_EXPORT_BOOKINGTYPES_DEFAULT'";
        DBManager::get()->exec($query);
        $query = "DELETE FROM `config_values`
                  WHERE `field` = 'RESOURCES_EXPORT_BOOKINGTYPES_DEFAULT'";
        DBManager::get()->exec($query);
    }
}
