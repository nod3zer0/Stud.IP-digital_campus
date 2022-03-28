<?php
final class ChangeSeminarUserCommentType extends Migration
{
    public function description()
    {
        return 'Changes the type of the comment column of tables seminar_user '
             . 'and admission_seminar_user to VARCHAR(255) instead of TINYTEXT';
    }

    protected function up()
    {
        $query = "ALTER TABLE `admission_seminar_user`
                  MODIFY COLUMN `comment` VARCHAR(255) NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `seminar_user`
                  MODIFY COLUMN `comment` VARCHAR(255) NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        // Column seminar_user does not need to be changed since it's already
        // VARCHAR(255) in db/studip.sql

        $query = "ALTER TABLE `admission_seminar_user`
                  MODIFY COLUMN `comment` TINYTEXT";
        DBManager::get()->exec($query);
    }
}
