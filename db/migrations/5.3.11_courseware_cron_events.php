<?php

require_once('lib/cronjobs/courseware.php');

class CoursewareCronEvents extends Migration
{
    public function description()
    {
        return 'Prepares database tables for storing timestamps of courseware cron events, ' .
            'like certificate sending and reminders.';
    }

    public function up()
    {
        // Create a table for certificates
        DBManager::get()->exec("CREATE TABLE IF NOT EXISTS `cw_certificates` (
            `id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `user_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `mkdate` INT(11) NOT NULL,
            PRIMARY KEY (`id`),
            INDEX index_user_id (`user_id`),
            INDEX index_course_id (`course_id`),
            INDEX index_user_ourse (`user_id`, `course_id`)
        )");

        CoursewareCronjob::register()->schedulePeriodic(41, 1)->activate();
    }

    public function down()
    {
        CoursewareCronjob::unregister();

        DBManager::get()->exec("DROP TABLE IF EXISTS `cw_certificates`");

        $fields = [
            'COURSEWARE_CERTIFICATE_SETTINGS',
            'COURSEWARE_REMINDER_SETTINGS',
            'COURSEWARE_RESET_PROGRESS_SETTINGS',
            'COURSEWARE_LAST_REMINDER',
            'COURSEWARE_LAST_PROGRESS_RESET'
        ];
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` IN (:fields)",
            ['fields' => $fields]);
        DBManager::get()->execute("DELETE FROM `config_values` WHERE `field` IN (:fields)",
            ['fields' => $fields]);
    }

}
