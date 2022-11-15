<?php
final class AddConsultationLockTime extends Migration
{
    public function description()
    {
        return 'Adds a lock time for consultation blocks that prevents slots '
             . 'from being booked based on the current time.';
    }

    protected function up()
    {
        $query = "ALTER TABLE `consultation_blocks`
                  ADD COLUMN `lock_time` INT(11) UNSIGNED DEFAULT NULL AFTER `size`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `consultation_blocks`
                  DROP COLUMN `lock_time`";
        DBManager::get()->exec($query);
    }
}
