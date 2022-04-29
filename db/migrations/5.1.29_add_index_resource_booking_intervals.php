<?php

class AddIndexResourceBookingIntervals extends Migration
{
    public function description()
    {
        return 'add index for booking_id to resource_booking_intervals';
    }

    public function up()
    {
        $db = DBManager::get();

        // avoid running this migration twice
        $sql = "SHOW INDEX FROM resource_booking_intervals WHERE Key_name = 'booking_id'";
        $result = $db->query($sql);

        if ($result && $result->rowCount() > 0) {
            return;
        }

        // index "assign_object_id" may not exist (depending on upgrade path)
        $sql = "SHOW INDEX FROM resource_booking_intervals WHERE Key_name = 'assign_object_id'";
        $result = $db->query($sql);

        if ($result && $result->rowCount() > 0) {
            $sql = 'ALTER TABLE resource_booking_intervals DROP INDEX assign_object_id';
            $db->exec($sql);
        }

        $sql = 'ALTER TABLE resource_booking_intervals ADD INDEX booking_id (booking_id)';
        $db->exec($sql);
    }

    public function down()
    {
        $db = DBManager::get();

        $query = 'ALTER TABLE resource_booking_intervals DROP INDEX booking_id';
        $db->exec($query);
    }
}
