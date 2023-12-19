<?php

class AddSiteinfoDraftField extends Migration {

    public function description()
    {
        return 'Creates configuration for sites to be in draft mode';
    }

    public function up()
    {
        DBManager::get()->exec("ALTER TABLE `siteinfo_details` ADD `draft_status` TINYINT NOT NULL DEFAULT '0' AFTER `position`");
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `siteinfo_details` DROP COLUMN `draft_status`");
    }



}
