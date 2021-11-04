<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/132
 */
final class ConsultationMultipleResponsibleRanges extends Migration
{
    public function description()
    {
        return 'Adjust database to allow multiple responsible ranges for consultations';
    }

    protected function up()
    {
        $query = "CREATE TABLE IF NOT EXISTS `consultation_responsibilities` (
                    `block_id` INT(11) UNSIGNED NOT NULL,
                    `range_id` CHAR(32) CHARSET latin1 COLLATE latin1_bin NOT NULL,
                    `range_type` ENUM('user', 'institute', 'statusgroup') CHARSET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`block_id`, `range_id`, `range_type`)
                  )";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `consultation_events` (
                    `slot_id` INT(11) UNSIGNED NOT NULL,
                    `user_id` CHAR(32) CHARSET latin1 COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL,
                    PRIMARY KEY (`slot_id`, `user_id`)
                  )";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `consultation_responsibilities` (
                     `block_id`, `range_id`, `range_type`, `mkdate`
                  )
                  SELECT `block_id`, `teacher_id`, 'user', UNIX_TIMESTAMP()
                  FROM `consultation_blocks`
                  WHERE `teacher_id` IS NOT NULL";
        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `consultation_events` (
                     `slot_id`, `user_id`, `event_id`, `mkdate`
                  )
                  SELECT `slot_id`, `teacher_id`, `teacher_event_id`, UNIX_TIMESTAMP()
                  FROM `consultation_blocks`
                  JOIN `consultation_slots` USING (`block_id`)
                  WHERE `teacher_event_id` IS NOT NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_blocks`
                  DROP COLUMN `teacher_id`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_slots`
                  DROP COLUMN `teacher_event_id`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `consultation_slots`
                  ADD COLUMN `teacher_event_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL AFTER `note`";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `consultation_blocks`
                  ADD COLUMN `teacher_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->exec($query);

        $query = "UPDATE `consultation_slots` AS cs
                  JOIN `consultation_events` AS ce USING (`slot_id`)
                  JOIN `consultation_blocks` AS cb USING (`block_id`)
                  SET cs.`teacher_event_id` = ce.`event_id`
                  WHERE cb.`range_type` = 'user'
                    AND cs.`slot_id` IN (
                        SELECT `slot_id`
                        FROM `consultation_events`
                        GROUP BY `slot_id`
                        HAVING COUNT(*) = 1
                    )";
        DBManager::get()->exec($query);

        $query = "UPDATE `consultation_blocks` AS cb
                  JOIN `consultation_responsibilities` AS cr USING (`block_id`)
                  SET cb.`teacher_id` = cr.`range_id`
                  WHERE cb.`block_id` IN (
                        SELECT `block_id`
                        FROM `consultation_responsibilities` AS cr2
                        JOIN `consultation_blocks` AS cb USING (`block_id`)
                        WHERE cr2.`range_type` = 'user'
                        GROUP BY `block_id`
                        HAVING COUNT(DISTINCT cr.`range_id`) = 1
                    )";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `consultation_events`";
        DBManager::get()->exec($query);

        $query = "DROP TABLE IF EXISTS `consultation_responsibilities`";
        DBManager::get()->exec($query);
    }
}
