<?php
final class RemoveDescriptionFields extends Migration
{
    public function description()
    {
        return 'Removes the unused database columns `termine`.`description`, `ex_termine`.`description` and `semester_data`.`description`';
    }

    protected function up()
    {
        $query = "ALTER TABLE `termine`
                  DROP COLUMN `description`";
        DBManager::get()->exec($query);
        $query = "ALTER TABLE `ex_termine`
                  DROP COLUMN `description`";
        DBManager::get()->exec($query);
        $query = "ALTER TABLE `semester_data`
                  DROP COLUMN `description`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `termine`
                  ADD COLUMN `description` TEXT AFTER `content`";
        DBManager::get()->exec($query);
        $query = "ALTER TABLE `ex_termine`
                  ADD COLUMN `description` TEXT AFTER `content`";
        DBManager::get()->exec($query);
        $query = "ALTER TABLE `semester_data`
                  ADD COLUMN `description` TEXT NOT NULL AFTER `name`";
        DBManager::get()->exec($query);
    }
}
