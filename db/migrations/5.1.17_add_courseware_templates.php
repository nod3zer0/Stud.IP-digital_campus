<?php

class AddCoursewareTemplates extends \Migration
{
    public function description()
    {
        return 'Create Courseware template database tables';
    }

    public function up()
    {
        $db = \DBManager::get();

        $db->exec("CREATE TABLE `cw_templates` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `purpose` ENUM('content', 'template', 'oer', 'portfolio', 'draft', 'other') COLLATE latin1_bin,
            `structure` MEDIUMTEXT NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`)
            )
        ");

    }

    public function down()
    {
        $db = \DBManager::get();

        $db->exec("DROP TABLE IF EXISTS `cw_templates`");
    }
}
