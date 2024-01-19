<?php
final class ChangeCollationForWikiTablesUserId extends Migration
{
    protected function up()
    {
        $query = "ALTER TABLE `wiki_versions`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) COLLATE `latin1_bin` NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_pages`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) COLLATE `latin1_bin` NOT NULL";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `wiki_versions`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `wiki_pages`
                  CHANGE COLUMN `user_id` `user_id` CHAR(32) NOT NULL";
        DBManager::get()->exec($query);
    }
}
