<?php
final class AddFeedbackAnonymousEntries extends Migration
{
    public function description()
    {
        return 'Extend feedback tables for anonymous entries';
    }

    public function up()
    {
        \DBManager::get()->exec("ALTER TABLE `feedback` 
            ADD `anonymous_entries` TINYINT(1) NOT NULL DEFAULT 0 
            AFTER `commentable`
        ");

        \DBManager::get()->exec("ALTER TABLE `feedback_entries` 
            ADD `anonymous` TINYINT(1) NOT NULL DEFAULT 0
            AFTER `rating`
        ");
    }

    public function down()
    {
        \DBManager::get()->exec("ALTER TABLE `feedback` DROP `anonymous_entries`");
        \DBManager::get()->exec("ALTER TABLE `feedback_entries` DROP `anonymous`");
    }
}
