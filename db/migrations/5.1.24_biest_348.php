<?php
class Biest348 extends Migration
{
    public function description ()
    {
        return 'Adds a column to the resources table to mark resources as lockable, default is 1.';
    }

    public function up()
    {
        if ($this->columnExists()) {
            return;
        }

        $query = 'ALTER TABLE `resources`
                  ADD `lockable` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `requestable`';
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = 'ALTER TABLE `resources` DROP `lockable`';
        DBManager::get()->exec($query);
    }

    private function columnExists()
    {
        return DBManager::get()->fetchFirst("SHOW COLUMNS FROM `resources` LIKE 'lockable'");
    }
}
