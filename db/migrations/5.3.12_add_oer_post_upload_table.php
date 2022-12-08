<?php
class AddOerPostUploadTable extends Migration
{
    public function description()
    {
        return "Adds table to create oer upload reminders and entry to cronjob schedule and task and config option";
    }

    public function up()
    {
        $db = DBmanager::Get();

        $db->exec("CREATE TABLE IF NOT EXISTS `oer_post_upload` (
                `file_ref_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin,
                `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin,
                `reminder_date` int unsigned,
                `mkdate` int(11) NOT NULL,
                `chdate` int(11) NOT NULL,
                PRIMARY KEY (`user_id`, `file_ref_id`)
            )");

        // Add default cron tasks and schedules
        $new_job = [
            'filename'    => 'lib/cronjobs/remind_oer_upload.class.php',
            'class'       => RemindOerUpload::class,
            'priority'    => 'normal',
            'minute'      => '0',
            'hour'        => '1',
            'active'      => '1'
        ];

        $query = "INSERT IGNORE INTO `cronjobs_tasks`
                    (`task_id`, `filename`, `class`, `active`)
                  VALUES (:task_id, :filename, :class, 1)";
        $task_statement = DBManager::get()->prepare($query);

        $query = "INSERT IGNORE INTO `cronjobs_schedules`
                    (`schedule_id`, `task_id`, `parameters`, `priority`,
                     `type`, `minute`, `hour`, `mkdate`, `chdate`,
                     `last_result`, `active`)
                  VALUES (:schedule_id, :task_id, '[]', :priority, 'periodic',
                          :minute, :hour, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                          NULL, :active)";
        $schedule_statement = DBManager::get()->prepare($query);


        $task_id = md5(uniqid('task', true));

        $task_statement->execute([
            ':task_id'  => $task_id,
            ':filename' => $new_job['filename'],
            ':class'    => $new_job['class'],
        ]);

        $schedule_id = md5(uniqid('schedule', true));
        $schedule_statement->execute([
            ':schedule_id' => $schedule_id,
            ':task_id'     => $task_id,
            ':priority'    => $new_job['priority'],
            ':hour'        => $new_job['hour'],
            ':minute'      => $new_job['minute'],
            ':active'      => $new_job['active']
        ]);

        $query = "INSERT IGNORE INTO `config`
                  SET `field` = :field,
                      `value` = :value,
                      `type` = :type,
                      `range` = :range,
                      `section` = :section,
                      `mkdate` = UNIX_TIMESTAMP(),
                      `chdate` = UNIX_TIMESTAMP(),
                      `description` = :description";
        $config_statement = DBManager::get()->prepare($query);

        $config_statement->execute([
            ':field'       => 'OER_ENABLE_POST_UPLOAD',
            ':value'       => '1',
            ':type'        => 'boolean',
            ':range'       => 'global',
            ':section'     => 'OERCampus',
            ':description' => 'Post-Upload-Dialog nach Hochladen einer Datei erlauben?',
        ]);

    }

    public function down()
    {
        CronjobTask::deleteBySQL('class = ?', [RemindOerUpload::class]);

        $query = "DROP TABLE `oer_post_upload`";
        DBManager::get()->exec($query);

        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'OER_ENABLE_POST_UPLOAD'";
        DBManager::get()->exec($query);
    }

}
