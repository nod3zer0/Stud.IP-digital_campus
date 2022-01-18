<?php
class Biest149 extends Migration
{
    public function description()
    {
        return "change log message for RES_REQUEST_DENY";
    }

    public function up()
    {
      DBManager::get()->exec("UPDATE `log_actions` SET `info_template` = '%user lehnt Raumanfrage für %sem(%affected), Raum: %res(%coaffected) ab. %info' WHERE `log_actions`.`action_id` = '9179d3cf4e0353f9874bcde072d12b30'");
    }

}

