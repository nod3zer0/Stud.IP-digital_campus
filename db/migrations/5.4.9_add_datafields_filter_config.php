<?php

class AddDatafieldsFilterConfig extends Migration
{
    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'ADMIN_COURSES_DATAFIELDS_FILTERS',
            ':description' => 'Für Admins, Roots und DedicatedAdmins können hier die Datenfelder gespeichert werden, nach denen die Veranstaltungen gefiltert werden sollen.',
            ':range'       => 'user',
            ':type'        => 'array',
            ':value'       => '[]'
        ]);
    }

    protected function down()
    {
        DBManager::get()->prepare('
            DELETE FROM `config` WHERE `field` = "ADMIN_COURSES_DATAFIELDS_FILTERS"
        ');
    }
}
