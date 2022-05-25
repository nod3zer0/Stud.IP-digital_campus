<?php

class Biest707 extends Migration
{
    public function description()
    {
        return 'Removes leftover entries of deleted course dates from resource request appointments';
    }
    
    public function up()
    {
        DBManager::get()->exec("DELETE FROM resource_request_appointments WHERE appointment_id NOT IN (SELECT termin_id FROM termine)");
    }
}
