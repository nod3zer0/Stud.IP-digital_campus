<?php

class ResizeOerContentTypeLength extends \Migration
{
    public function description()
    {
        return 'Makes the database field of oer_material`s content_type longer so that it is conform to the rfc.';
    }

    public function up()
    {
        \DBManager::get()->exec("
            ALTER TABLE `oer_material`
            CHANGE COLUMN `content_type` `content_type` varchar(256) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL
        ");
    }

    public function down()
    {
        \DBManager::get()->exec("
            ALTER TABLE `oer_material`
            CHANGE COLUMN `content_type` `content_type` varchar(64) NOT NULL
        ");
    }
}
