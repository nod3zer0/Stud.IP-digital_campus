<?php
final class CleanupForumTables extends Migration
{
    protected function up()
    {
        $query = "DELETE FROM `forum_abo_users`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id`
                      FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);

        $query = "DELETE FROM `forum_favorites`
                  WHERE `user_id` NOT IN (
                      SELECT `user_id`
                      FROM `auth_user_md5`
                  )";
        DBManager::get()->exec($query);
    }
}
