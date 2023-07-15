<?php

final class ExendUserInfoHobbySize extends Migration
{
    public function description()
    {
        return 'Increase max lenghth of column hobby in user_info';
    }

    public function up()
    {
        DBManager::get()->exec(
            "ALTER TABLE `user_info` CHANGE `hobby` `hobby` mediumtext NOT NULL"
        );
    }

    public function down()
    {
        DBManager::get()->exec(
            "ALTER TABLE `user_info` CHANGE `hobby` `hobby` VARCHAR(255) NOT NULL DEFAULT ''"
        );
    }
}
