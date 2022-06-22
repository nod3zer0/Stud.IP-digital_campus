<?php


class AddSemwechselField extends Migration
{
    public function description()
    {
        return 'Add field sem_wechsel to table semester_data';
    }


    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `semester_data`
            ADD `sem_wechsel` INT(11) UNSIGNED NULL AFTER `ende`"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `semester_data` DROP `sem_wechsel`"
        );

    }
}
