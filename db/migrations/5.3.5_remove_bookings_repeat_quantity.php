<?php

class RemoveBookingsRepeatQuantity extends Migration
{
    public function description()
    {
        return 'Remove unused column repeat_quantity from table resource_bookings';
    }

    protected function up()
    {
        $query = 'ALTER TABLE resource_bookings DROP COLUMN repeat_quantity';
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = 'ALTER TABLE resource_bookings ADD COLUMN repeat_quantity int(2) AFTER repeat_end';
        DBManager::get()->exec($query);
    }
}
