<?php
class CourseMemberAdmissionCourseIdType extends Migration
{
    public function description()
    {
        return "Save course ids as JSON instead of plain text.";
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `coursememberadmissions`
                ADD COLUMN `courses` JSON default NULL AFTER `course_id`
        ");

        DBManager::get()->exec("UPDATE `coursememberadmissions`
                SET `courses` = JSON_ARRAY_APPEND('[]', '$', `course_id`)
                WHERE `course_id` != ''
                ");

        DBManager::get()->exec("ALTER TABLE `coursememberadmissions`
                DROP COLUMN `course_id`");

    }

    public function down()
    {
        $query = "ALTER TABLE `coursememberadmissions`
                  ADD COLUMN `course_id` CHAR(32) CHARACTER SET `latin1` COLLATE `latin1_bin` NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);

        $query = "UPDATE `coursememberadmissions`
                  SET `course_id` = IFNULL(JSON_EXTRACT(`courses`, '$[0]'), '')";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `coursememberadmissions`
                  DROP COLUMN `courses`";
        DBManager::get()->exec($query);
    }

}
