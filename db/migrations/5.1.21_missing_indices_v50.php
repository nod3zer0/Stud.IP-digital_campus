<?php
final class MissingIndicesV50 extends Migration
{
    use DatabaseMigrationTrait;

    public function description()
    {
        return 'Add missing indices on some tables';
    }

    public function up()
    {
        // avoid running this migration twice
        if ($this->keyExists('mvv_files_ranges', 'range_id')) {
            return;
        }

        $query = "CREATE INDEX `range_id` ON `mvv_files_ranges` (`range_id`)";
        DBManager::get()->exec($query);

        $query = "CREATE INDEX `context_query` ON `activities` (`context`, `context_id`, `mkdate`)";
        DBManager::get()->exec($query);

        $query = "CREATE INDEX `user_id` ON `comments` (`user_id`)";
        DBManager::get()->exec($query);

        $query = "CREATE INDEX `user_id` ON `file_refs` (`user_id`)";
        DBManager::get()->exec($query);

        $query = "CREATE INDEX `user_id` ON `news` (`user_id`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `news` DROP INDEX `user_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `file_refs` DROP INDEX `user_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `comments` DROP INDEX `user_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `activities` DROP INDEX `context_query`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `mvv_files_ranges` DROP INDEX `range_id`";
        DBManager::get()->exec($query);
    }
}
