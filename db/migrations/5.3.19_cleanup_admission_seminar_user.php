<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2441
 */
final class CleanupAdmissionSeminarUser extends Migration
{
    public function description()
    {
        return 'Removes entries from table admission_seminar_user that are '
             . 'already in table seminar_user';
    }

    protected function up()
    {
        // Fetch affected course ids
        $query = "SELECT DISTINCT `seminar_id`
                  FROM `admission_seminar_user`
                  JOIN `seminar_user` USING (`seminar_id`, `user_id`)";
        $course_ids = DBManager::get()->fetchFirst($query);

        // Remove all entries that are already in seminar_user
        $query = "DELETE `admission_seminar_user`
                  FROM `admission_seminar_user`
                  JOIN `seminar_user` USING (`seminar_id`, `user_id`)";
        DBManager::get()->exec($query);

        // Adjust positions in admission_seminar_user for all affected courses
        foreach ($course_ids as $course_id) {
            $query = "SELECT `user_id`
                      FROM `admission_seminar_user`
                      WHERE `seminar_id` = ? AND status = 'awaiting'
                      ORDER BY `position`";
            $user_ids = DBManager::get()->fetchFirst($query, [$course_id]);

            foreach ($user_ids as $index => $user_id) {
                $query = "UPDATE `admission_seminar_user`
                          SET `position` = ?
                          WHERE `seminar_id` = ? AND `user_id` = ?";
                DBManager::get()->execute($query, [
                    $index + 1,
                    $course_id,
                    $user_id
                ]);
            }
        }
    }
}
