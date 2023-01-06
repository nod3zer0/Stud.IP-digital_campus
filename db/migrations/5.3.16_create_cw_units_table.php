<?php

class CreateCwUnitsTable extends Migration
{
    public function description()
    {
        return 'create table for courseware units';
    }

    public function up()
    {
        $db = DBManager::get();

        $query = "CREATE TABLE IF NOT EXISTS `cw_units` (
            `id`                      INT(11) NOT NULL AUTO_INCREMENT,
            `range_id`                CHAR(32) COLLATE latin1_bin NULL,
            `range_type`              ENUM('course', 'user') COLLATE latin1_bin,
            `structural_element_id`   INT(11) NOT NULL,
            `content_type`            ENUM('courseware') CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `public`                  TINYINT(4) NOT NULL DEFAULT '1',
            `creator_id`              CHAR(32) COLLATE latin1_bin DEFAULT NULL,
            `release_date`            INT(11) UNSIGNED DEFAULT NULL,
            `withdraw_date`           INT(11) UNSIGNED NOT NULL,
            `mkdate`                  INT(11) UNSIGNED NOT NULL,
            `chdate`                  INT(11) UNSIGNED NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_range_id (`range_id`),
            INDEX index_structural_element_id (`structural_element_id`)
        )";
        $db->exec($query);

        //get all courseware root nodes
        $query = "SELECT * FROM `cw_structural_elements` WHERE `parent_id` IS NULL";
        $cw_root_nodes = $db->fetchAll($query);

        // create unit for each courseware root node
        $insert = $db->prepare(
            "INSERT INTO `cw_units` (`range_id`, `range_type`, `structural_element_id`, `content_type`, `public`, `creator_id`) 
             VALUES (?, ?, ?, 'courseware', true, ?)"
        );
        foreach ($cw_root_nodes as $courseware) {
            $insert->execute([$courseware['range_id'], $courseware['range_type'], $courseware['id'], $courseware['owner_id']]);
        }
    }

    public function down()
    {
        $db = \DBManager::get();
        $db->exec('DROP TABLE IF EXISTS `cw_units`');
    }
}
