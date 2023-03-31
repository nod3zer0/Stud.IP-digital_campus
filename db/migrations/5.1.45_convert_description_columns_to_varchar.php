<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2497
 */
final class ConvertDescriptionColumnsToVarchar extends Migration
{
    public function description()
    {
        return 'Convert the `description` columns in tables `termine`, `ex_termine`and `semester_data` to VARCHAR.';

    }

    protected function up()
    {
        $query = "ALTER TABLE `termine`
                  CHANGE COLUMN `description` `description` VARCHAR(255) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `ex_termine`
                  CHANGE COLUMN `description` `description` VARCHAR(255) DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `semester_data`
                  CHANGE COLUMN `description` `description` VARCHAR(255) NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `semester_data`
                  CHANGE COLUMN `description` `description` TEXT NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `ex_termine`
                  CHANGE COLUMN `description` `description` TEXT DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `termine`
                  CHANGE COLUMN `description` `description` TEXT DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
