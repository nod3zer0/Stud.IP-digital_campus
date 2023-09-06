<?

final class RestoreStudipObjectId extends Migration
{

    public function description()
    {
        return 'Restores the studip_object_id column for sem_tree';
    }

    protected function up()
    {
        // Add database column for sem_tree institute assignments.
        DBManager::get()->exec("ALTER TABLE `sem_tree` ADD
            `studip_object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `name`");
        // Add index for studip_object_id.
        DBManager::get()->exec("ALTER TABLE `sem_tree` ADD INDEX `studip_object_id` (`studip_object_id`)");
    }

    protected function down()
    {
        // Remove institute assignments for sem_tree entries.
        DBManager::get()->exec("ALTER TABLE `sem_tree` DROP `studip_object_id`");
    }

}
