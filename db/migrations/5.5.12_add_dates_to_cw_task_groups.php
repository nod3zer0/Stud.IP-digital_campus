<?php
class AddDatesToCwTaskGroups extends Migration
{
    public function description()
    {
        return 'Add start_date and end_date to table cw_task_groups.';
    }

    public function up()
    {
        $dbm = \DBManager::get();
        $dbm->exec(
            "ALTER TABLE `cw_task_groups`
             ADD `start_date` INT NOT NULL AFTER `title`,
             ADD `end_date` INT NOT NULL AFTER `start_date`"
        );
        $dbm->exec('UPDATE `cw_task_groups` SET `start_date`=`mkdate`');
        $dbm->exec(
            'UPDATE `cw_task_groups` AS tg SET tg.`end_date` = ( SELECT MAX(t.`submission_date`) FROM `cw_tasks` t WHERE t.`task_group_id` = tg.`id` )'
        );
        $dbm->exec('ALTER TABLE `cw_tasks` DROP `submission_date`');
    }

    public function down()
    {
        $dbm = \DBManager::get();
        $dbm->exec("ALTER TABLE `cw_tasks` ADD `submission_date` int(11) NOT NULL AFTER `solver_type`");
        $dbm->exec('UPDATE `cw_tasks` AS t INNER JOIN cw_task_groups tg ON t.`task_group_id` = tg.`id` SET t.`submission_date` = tg.`end_date`');
        $dbm->exec(
            'ALTER TABLE `cw_task_groups`
             DROP `start_date`,
             DROP `end_date`'
        );
    }
}
