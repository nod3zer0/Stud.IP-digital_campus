<?php


class AddMatriculationNumber extends Migration
{
    public function description()
    {
        return 'Add auth_user_md5.matriculation_number';
    }

    protected function up()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `auth_user_md5`
            ADD COLUMN matriculation_number VARCHAR(255) NULL DEFAULT NULL"
        );
    }

    protected function down()
    {
        $db = DBManager::get();
        $db->exec(
            "ALTER TABLE `auth_user_md5`
            DROP COLUMN matriculation_number"
        );
    }
}
