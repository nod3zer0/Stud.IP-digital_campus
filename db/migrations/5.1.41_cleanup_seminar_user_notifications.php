<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2141
 */
final class CleanupSeminarUserNotifications extends Migration
{
    public function description()
    {
        return "Removes all entries from `seminar_user_notifications` that are not in `seminar_user` as well";
    }

    protected function up()
    {
        $query = "DELETE `seminar_user_notifications`
                          FROM `seminar_user_notifications`
                          LEFT JOIN `seminar_user` USING(`user_id`, `Seminar_id`)
                          WHERE `seminar_user`.`user_id` IS NULL";
        DBManager::get()->exec($query);
    }
}
