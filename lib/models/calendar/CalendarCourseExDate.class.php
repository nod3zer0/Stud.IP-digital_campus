<?php

/**
 * CalendarCourseExDate is a specialisation of CourseExDate for
 * cancelled course dates that are displayed in the personal calendar.
 */
class CalendarCourseExDate extends CourseExDate
{
    public static function getEvents(DateTime $begin, DateTime $end, string $range_id): array
    {
        return parent::findBySQL(
            "JOIN `seminar_user`
               ON `seminar_user`.`seminar_id` = `ex_termine`.`range_id`
             WHERE `seminar_user`.`user_id` = :user_id
               AND `seminar_user`.`bind_calendar` = '1'
               AND `ex_termine`.`date` BETWEEN :begin AND :end
               AND `ex_termine`.`content` <> ''
               AND (
                   IFNULL(`ex_termine`.`metadate_id`, '') = ''
                   OR `ex_termine`.`metadate_id` NOT IN (
                       SELECT `metadate_id`
                       FROM `schedule_seminare`
                       WHERE `user_id` = :user_id
                         AND `visible` = 0
                 )
             )
             ORDER BY date",
            [
                'begin'   => $begin->getTimestamp(),
                'end'     => $end->getTimestamp(),
                'user_id' => $range_id
            ]
        );
    }
}
