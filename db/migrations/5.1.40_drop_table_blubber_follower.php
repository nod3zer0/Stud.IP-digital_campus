<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2082
 */
final class DropTableBlubberFollower extends Migration
{
    public function description()
    {
        return 'Removes unused table "blubber_follower"';
    }

    protected function up()
    {
        $query = "DROP TABLE IF EXISTS `blubber_follower`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "CREATE TABLE IF NOT EXISTS `blubber_follower` (
                    `studip_user_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `external_contact_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `left_follows_right` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                    KEY `studip_user_id` (`studip_user_id`),
                    KEY `external_contact_id` (`external_contact_id`)
                  )";
        DBManager::get()->exec($query);
    }
}
