<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/merge_requests/2071/diffs#note_84752
 */
final class AddMissingConfigurationsUsedInAdminCourses extends Migration
{
    public function description()
    {
        return 'Adds the missing configurations for ADMIN_COURSES_SEARCHTEXT, '
             . 'MY_COURSES_SELECTED_CYCLE, MY_COURSES_SELECTED_STGTEIL, '
             . 'ADMIN_COURSES_TEACHERFILTER and MY_COURSES_TYPE_FILTER.';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`, `description`
                  ) VALUES (
                   :field, :value, :type, 'user', '',
                   UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description
                  )";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([
            ':field' => 'ADMIN_COURSES_SEARCHTEXT',
            ':value' => '',
            ':type'  => 'string',
            ':description' => 'Speichert den auf der Veranstaltungsübersicht für Admins eingegebenen Suchtext',
        ]);

        $statement->execute([
            ':field' => 'MY_COURSES_SELECTED_CYCLE',
            ':value' => '',
            ':type'  => 'string',
            ':description' => 'Das auf der Veranstaltungsübersicht für Admins gewählte Semester',
        ]);

        $statement->execute([
            ':field' => 'MY_COURSES_SELECTED_STGTEIL',
            ':value' => '',
            ':type'  => 'string',
            ':description' => 'Der auf der Veranstaltungsübersicht für Admins gewählte Studiengangsteil',
        ]);

        $statement->execute([
            ':field' => 'ADMIN_COURSES_TEACHERFILTER',
            ':value' => '',
            ':type'  => 'string',
            ':description' => 'Der auf der Veranstaltungsübersicht für Admins gewählte Filter auf Lehrende',
        ]);

        $statement->execute([
            ':field' => 'MY_COURSES_TYPE_FILTER',
            ':value' => '',
            ':type'  => 'string',
            ':description' => 'Der auf der Veranstaltungsübersicht für Admins gewählte Filter auf Veranstaltungstypen',
        ]);
    }

    protected function down()
    {
        $query = "DELETE FROM `config` WHERE `field` = :field";
        $statement = DBManager::get()->prepare($query);

        $statement->execute([':field' => 'ADMIN_COURSES_SEARCHTEXT']);
        $statement->execute([':field' => 'MY_COURSES_SELECTED_CYCLE']);
        $statement->execute([':field' => 'MY_COURSES_SELECTED_STGTEIL']);
        $statement->execute([':field' => 'ADMIN_COURSES_TEACHERFILTER']);
        $statement->execute([':field' => 'MY_COURSES_TYPE_FILTER']);
    }
}
