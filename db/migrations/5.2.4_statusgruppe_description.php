<?php
class StatusgruppeDescription extends Migration
{
    public function description()
    {
        return 'add optional description to status groups';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE statusgruppen ADD description TEXT DEFAULT NULL AFTER name');
    }

    public function down()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE statusgruppen DROP description');
    }
}
