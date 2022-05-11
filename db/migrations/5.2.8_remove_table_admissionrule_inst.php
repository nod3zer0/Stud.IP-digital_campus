<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/973
 */
final class RemoveTableAdmissionruleInst extends Migration
{
    public function description()
    {
        return 'Removes the unused table admissionrule_inst';
    }

    protected function up()
    {
        $query = "DROP TABLE IF EXISTS `admissionrule_inst`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "CREATE TABLE IF NOT EXISTS `admissionrule_inst` (
                    `rule_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `institute_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                    `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    PRIMARY KEY (`rule_id`,`institute_id`)
                  )";
        DBManager::get()->exec($query);
    }
}
