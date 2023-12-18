<?php

final class CoursewareAddOptionalComments extends Migration
{
    public function description()
    {
        return 'Add column commentable to cw_blocks and cw_structural_elements';
    }

    protected function up()
    {
        DBManager::get()->exec("ALTER TABLE `cw_blocks` ADD `commentable` TINYINT(1) NOT NULL AFTER `visible`"); 
        DBManager::get()->exec("ALTER TABLE `cw_structural_elements` ADD `commentable` TINYINT(1) NOT NULL AFTER `public`"); 
    }

    protected function down()
    {
        DBManager::get()->exec("ALTER TABLE `cw_blocks` DROP `commentable`"); 
        DBManager::get()->exec("ALTER TABLE `cw_structural_elements` DROP `commentable`"); 
    }
}
