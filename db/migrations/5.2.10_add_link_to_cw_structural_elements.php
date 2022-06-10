<?php

final class AddLinkToCwStructuralElements extends Migration
{
    public function description()
    {
        return 'Adds columns for link funtions to cw_structural_elements table';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `cw_structural_elements`
                ADD COLUMN `is_link` tinyint(1) NOT NULL AFTER `parent_id`,
                ADD COLUMN `target_id` int(11) DEFAULT NULL AFTER `is_link`
        ");
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `cw_structural_elements`
                DROP COLUMN `is_link`,
                DROP COLUMN `target_id`
        ");
    }
}
