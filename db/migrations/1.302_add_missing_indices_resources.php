<?php
/**
 * @license GPL2 or any later version
 * @see https://develop.studip.de/trac/ticket/10882
 */
class AddMissingIndicesResources extends Migration
{
    public function description()
    {
        return 'Adds missing indices for table `resource_temporary_permissions`';
    }

    public function up()
    {
        // avoid running this migration twice
        $query = "SHOW INDEX FROM resource_temporary_permissions WHERE Key_name = 'user_id'";
        $result = DBManager::get()->query($query);

        if ($result && $result->rowCount() > 0) {
            return;
        }

        $query = "ALTER TABLE `resource_temporary_permissions` ADD INDEX (`user_id`)";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_temporary_permissions` ADD INDEX (`resource_id`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `resource_temporary_permissions`
                  DROP INDEX `user_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `resource_temporary_permissions`
                  DROP INDEX `resource_id`";
        DBManager::get()->exec($query);
    }
}
