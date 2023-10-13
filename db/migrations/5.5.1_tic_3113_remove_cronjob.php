<?php

class Tic3113RemoveCronjob extends Migration
{
    public function description()
    {
        return 'Removes the cleanup cronjob for the table "object_user_visits"';
    }

    public function up()
    {
        $query = 'DELETE `cronjobs_tasks`, `cronjobs_schedules`, `cronjobs_logs`
                  FROM `cronjobs_tasks`
                  LEFT JOIN `cronjobs_schedules` USING (`task_id`)
                  LEFT JOIN `cronjobs_logs` USING (`schedule_id`)
                  WHERE `filename` = :filename';

        DBManager::get()->execute($query, [
            ':filename' => 'lib/cronjobs/clean_object_user_visits.php'
        ]);
    }

    public function down()
    {
        $query = 'INSERT INTO `cronjobs_tasks` (`task_id`, `filename`, `class`)
                  VALUES (:task_id, :filename, :class)';

        DBManager::get()->execute($query, [
            ':task_id'  => '7cb4134a91bd985263fd570c7560ad72',
            ':filename' => 'lib/cronjobs/clean_object_user_visits.php',
            ':class'    => 'CleanObjectUserVisits',
        ]);
    }
}
