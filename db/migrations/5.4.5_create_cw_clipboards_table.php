<?php

class CreateCwClipboardsTable extends Migration
{
    public function description()
    {
        return 'create table for courseware clipboards';
    }

    public function up()
    {
        $db = DBManager::get();

        $query = "CREATE TABLE IF NOT EXISTS `cw_clipboards` (
            `id`                    INT(11) NOT NULL AUTO_INCREMENT,
            `user_id`               CHAR(32) COLLATE latin1_bin NOT NULL,
            `name`                  VARCHAR(255) NOT NULL,
            `description`           MEDIUMTEXT NOT NULL,
            `block_id`              INT(11) NULL,
            `container_id`          INT(11) NULL,
            `structural_element_id` INT(11) NULL,
            `object_type`           ENUM('courseware-structural-elements', 'courseware-containers', 'courseware-blocks') COLLATE latin1_bin NOT NULL,
            `object_kind`           VARCHAR(255) COLLATE latin1_bin NOT NULL,
            `backup`                MEDIUMTEXT NOT NULL,
            `mkdate`                INT(11) UNSIGNED NOT NULL,
            `chdate`                INT(11) UNSIGNED NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_user_id (`user_id`)
        )";
        $db->exec($query);
    }

    public function down()
    {
        $db = \DBManager::get();
        $db->exec('DROP TABLE IF EXISTS `cw_clipboards`');
    }
}
