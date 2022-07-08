<?php

class AddIndexResourceBookingIntervals extends Migration
{
    use DatabaseMigrationTrait;

    public function description()
    {
        return 'add index for booking_id to resource_booking_intervals';
    }

    public function up()
    {
        // avoid running this migration twice
        if ($this->keyExists('resource_booking_intervals', 'booking_id')) {
            return;
        }

        // index "assign_object_id" may not exist (depending on upgrade path)
        if ($this->keyExists('resource_booking_intervals', 'assign_object_id')) {
            $sql = "ALTER TABLE resource_booking_intervals DROP INDEX assign_object_id";
            DBManager::get()->exec($sql);
        }

        $sql = "ALTER TABLE resource_booking_intervals ADD INDEX booking_id (booking_id)";
        DBManager::get()->exec($sql);
    }

    public function down()
    {
        $query = "ALTER TABLE resource_booking_intervals DROP INDEX booking_id";
        DBManager::get()->exec($query);
    }
}
