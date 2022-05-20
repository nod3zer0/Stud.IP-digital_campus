<?php

class AddTemplateToLogAction extends Migration
{
    public function description()
    {
        return 'Adds missing template to info_template';
    }
    public function up()
    {
        DBManager::get()->exec("UPDATE `log_actions` SET `info_template` = '%user ändert Berechtigung von %res(%affected): %info' WHERE `name` = 'RES_PERM_CHANGE'");
    }

    public function down()
    {
    }
}