<?php

/**
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @see https://gitlab.studip.de/studip/studip/-/issues/3435
 */
final class AddTutorMailingOptionToConsultationBlocks extends Migration
{
    public function description()
    {
        return 'Adds the flag "mail_to_tutors" to table "consultation_blocks"';
    }

    protected function up()
    {
        $query = "ALTER TABLE `consultation_blocks`
                  ADD COLUMN `mail_to_tutors` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `require_reason`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `consultation_blocks`
                  DROP COLUMN `mail_to_tutors`";
        DBManager::get()->exec($query);
    }

}
