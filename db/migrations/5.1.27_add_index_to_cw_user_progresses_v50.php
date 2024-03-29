<?php
final class AddIndexToCwUserProgressesV50 extends Migration
{
    use DatabaseMigrationTrait;

    public function description()
    {
        return 'Alter cw_user_progresses table, add index for block_id';
    }

    public function up()
    {
        // avoid running this migration twice
        if ($this->keyExists('cw_user_progresses', 'block_id')) {
            return;
        }

        $query = "ALTER TABLE `cw_user_progresses`
                  ADD INDEX `block_id` (`block_id`)";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `cw_user_progresses`
                  DROP INDEX `block_id`";
        DBManager::get()->exec($query);
    }
}
