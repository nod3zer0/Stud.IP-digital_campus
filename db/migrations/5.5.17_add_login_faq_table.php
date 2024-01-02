<?php

class AddLoginFaqTable extends Migration
{
    public function description()
    {
        return 'Create table for login page FAQ';
    }

    public function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `login_faq` (
                    `faq_id` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) NOT NULL,
                    `description` text NOT NULL,
                    PRIMARY KEY (`faq_id`)
                  )";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        DBManager::get()->exec('DROP TABLE IF EXISTS `login_faq`');

        $query = "DELETE FROM `i18n`
                  WHERE `table` = 'login_faq`";
        DBManager::get()->exec($query);
    }
}
