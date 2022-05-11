<?php


class AddCommentToHelpContent extends Migration
{
    public function description()
    {
        return 'Adds the column "comment" to the help_content table.';
    }


    protected function up()
    {
        DBManager::get()->exec(
            "ALTER IGNORE TABLE `help_content`
            ADD COLUMN comment TEXT NULL"
        );
    }


    protected function down()
    {
        DBManager::get()->exec(
            "ALTER IGNORE TABLE `help_content`
            DROP COLUMN comment"
        );
    }
}
