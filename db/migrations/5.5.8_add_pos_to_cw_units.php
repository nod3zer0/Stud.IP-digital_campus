<?php

final class AddPosToCwUnits extends Migration
{
    public function description()
    {
        return 'Add field pos to table cw_units';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec("
            ALTER TABLE `cw_units`
            ADD COLUMN `position` INT(11) DEFAULT NULL AFTER `content_type`
        ");
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec("
            ALTER TABLE `cw_units`
            DROP COLUMN `position`
        ");
    }
}
