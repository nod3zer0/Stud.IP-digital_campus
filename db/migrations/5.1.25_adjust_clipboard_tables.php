<?php
final class AdjustClipboardTables extends Migration
{
    public function description()
    {
        return 'Alter clipboard tables by fixing indices, column types and collations';
    }

    protected function up()
    {
        $query = "ALTER TABLE `clipboards`
                  CHANGE COLUMN `name` `name` VARCHAR(256) NOT NULL DEFAULT '',
                  ADD INDEX `user_id` (`user_id`)";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `clipboard_items`
                  CHANGE COLUMN `range_id` `range_id` CHAR(32) CHARACTER SET latin1 COLLATE `latin1_bin` NOT NULL,
                  ADD INDEX `clipboard_id` (`clipboard_id`),
                  ADD INDEX `range` (`range_id`,`range_type`)";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `clipboard_items`
                  CHANGE COLUMN `range_id` `range_id` VARCHAR(98) CHARACTER SET latin1 COLLATE `latin1_bin` NOT NULL,
                  DROP INDEX `clipboard_id`,
                  DROP INDEX `range`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `clipboards`
                  CHANGE COLUMN `name` `name` VARCHAR(256) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                  DROP INDEX `user_id`";
        DBManager::get()->exec($query);
    }
}
