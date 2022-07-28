<?php
class AddSeminareSemesterTable extends Migration
{

    public function description()
    {
        return 'Creates a better performing connection between courses and semesters.';
    }

    public function up()
    {
        DBManager::get()->exec(
            "CREATE TABLE IF NOT EXISTS `semester_courses` (
                `semester_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `course_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `mkdate` INT(10) NOT NULL DEFAULT '0',
                `chdate` INT(10) NOT NULL DEFAULT '0',
                PRIMARY KEY (`semester_id`, `course_id`)
            );"
        );

        DBManager::get()->exec("
            INSERT IGNORE INTO `semester_courses`
            (`semester_id`, `course_id`, `mkdate`, `chdate`)
            SELECT `semester_data`.`semester_id`, `seminare`.`Seminar_id`, `seminare`.`mkdate`, `seminare`.`chdate`
            FROM `seminare`
            INNER JOIN `semester_data` ON 
                `seminare`.`start_time` <= `semester_data`.`beginn` AND
                `semester_data`.`beginn` <= `seminare`.`start_time` + `seminare`.`duration_time` AND
                `seminare`.`duration_time` >= 0
        ");
    }

    public function down()
    {
        DBManager::get()->exec("DROP TABLE IF EXISTS `semester_courses`;");
    }
}
