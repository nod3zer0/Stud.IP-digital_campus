<?php

final class CleanupCwTasks extends Migration
{
    public function description()
    {
        return 'deletes unlinked entries';
    }

    public function up()
    {
        DBManager::get()->exec('
            DELETE FROM `cw_tasks`
            WHERE `solver_type` = "user"
              AND `solver_id` NOT IN (SELECT `user_id` FROM `auth_user_md5`)'
        );
        DBManager::get()->exec('
            DELETE FROM `cw_tasks`
            WHERE `solver_type` = "group"
               AND `solver_id` NOT IN (SELECT `statusgruppe_id` FROM `statusgruppen`)'
        );
    }
}
