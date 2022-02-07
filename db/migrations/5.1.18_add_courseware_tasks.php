<?php

class AddCoursewareTasks extends \Migration
{
    public function description()
    {
        return 'Create Courseware Task database tables and settings';
    }

    public function up()
    {
        $db = \DBManager::get();

        $db->exec("CREATE TABLE `cw_task_groups` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `seminar_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `lecturer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `target_id` int(11) NOT NULL,
            `task_template_id` int(11) NOT NULL,
            `solver_may_add_blocks` tinyint(1) NOT NULL,
            `title` varchar(255) NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_seminar_id (`seminar_id`),
            INDEX index_lecturer_id (`lecturer_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_tasks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_group_id` int(11) NOT NULL,
            `structural_element_id` int(11) NOT NULL,
            `solver_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `solver_type` ENUM('autor', 'group') COLLATE latin1_bin,
            `submission_date` int(11) NOT NULL,
            `submitted` tinyint(1) NOT NULL,
            `renewal` ENUM('pending', 'granted', 'declined') COLLATE latin1_bin,
            `renewal_date` int(11) NOT NULL,
            `feedback_id` int(11) NULL DEFAULT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_task_group_id (`task_group_id`),
            INDEX index_structural_element_id (`structural_element_id`),
            INDEX index_solver_id (`solver_id`)
            )
        ");

        $db->exec("CREATE TABLE `cw_task_feedbacks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `task_id` int(11) NOT NULL,
            `lecturer_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `content` MEDIUMTEXT NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_task_id (`task_id`),
            INDEX index_lecturer_id (`lecturer_id`)
            )
        ");

        $db->exec("ALTER TABLE `cw_structural_elements`
            CHANGE `purpose` `purpose` ENUM('content','draft','task','template','oer','other','portfolio')
            CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL;"
        );
    }

    public function down()
    {
        $db = \DBManager::get();

        $db->exec("DROP TABLE IF EXISTS `cw_tasks`, `cw_task_feedbacks`");
    }
}
