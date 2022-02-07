<?php

class AddCoursewareStructuralElementDiscussion extends \Migration
{
    public function description()
    {
        return 'Create Courseware structural element database tables for discussions';
    }

    public function up()
    {
        $db = \DBManager::get();

        $db->exec("CREATE TABLE `cw_structural_element_comments` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `structural_element_id` int(11) NOT NULL,
            `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `comment` MEDIUMTEXT NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_structural_element_id (`structural_element_id`),
            INDEX index_user_id (`user_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_structural_element_feedbacks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `structural_element_id` int(11) NOT NULL,
            `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `feedback` MEDIUMTEXT NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_structural_element_id (`structural_element_id`),
            INDEX index_user_id (`user_id`)
            )
        ");
    }

    public function down()
    {
        $db = \DBManager::get();

        $db->exec("DROP TABLE IF EXISTS `cw_structural_element_feedbacks`, `cw_structural_element_comments`");
    }
}