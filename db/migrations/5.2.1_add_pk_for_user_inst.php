<?php

class AddPkForUserInst extends Migration
{
    public function description()
    {
        return 'add simple pk for user_inst table';
    }

    public function up()
    {
        $db = DBManager::get();

        $sql = 'ALTER TABLE user_inst
                ADD id int(11) NOT NULL AUTO_INCREMENT FIRST,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (id),
                ADD UNIQUE KEY user_inst (Institut_id, user_id)';
        $db->exec($sql);
    }

    public function down()
    {
        $db = DBManager::get();

        $sql = 'ALTER TABLE user_inst
                DROP id,
                DROP KEY user_inst,
                ADD PRIMARY KEY (Institut_id, user_id)';
        $db->exec($sql);
    }
}
