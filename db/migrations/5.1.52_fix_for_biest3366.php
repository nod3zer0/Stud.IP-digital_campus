<?php

class FixForBiest3366 extends Migration
{
    public function description()
    {
        return 'Removes the database table "globalsearch_buzzwords"';
    }

    public function up()
    {
        DBManager::get()->exec('DROP TABLE `globalsearch_buzzwords`');
    }

    public function down()
    {
        DBManager::get()->exec("CREATE TABLE `globalsearch_buzzwords` (
            `id` CHAR(32) COLLATE latin1_bin NOT NULL,
            `rights` ENUM('user','autor','tutor','dozent','admin','root') NOT NULL DEFAULT 'user',
            `name` varchar(255) NOT NULL DEFAULT '',
            `buzzwords` varchar(2048) NOT NULL DEFAULT '',
            `subtitle` varchar(255) DEFAULT NULL,
            `url` varchar(2048) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`))");
    }
}
