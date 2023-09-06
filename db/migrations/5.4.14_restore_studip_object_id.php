<?

final class RestoreStudipObjectId extends Migration
{

    use DatabaseMigrationTrait;

    public function description()
    {
        return 'Restores the studip_object_id column for sem_tree';
    }

    protected function up()
    {
        if (!$this->columnExists('sem_tree', 'studip_object_id')) {
            // Add database column for sem_tree institute assignments.
            DBManager::get()->exec("ALTER TABLE `sem_tree` ADD
                `studip_object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `name`");
            // Add index for studip_object_id.
            DBManager::get()->exec("ALTER TABLE `sem_tree` ADD INDEX `studip_object_id` (`studip_object_id`)");
        }
    }

}
