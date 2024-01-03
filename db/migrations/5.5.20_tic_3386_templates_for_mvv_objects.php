<?php

class Tic3386TemplatesForMvvObjects extends Migration
{
    public function description()
    {
        return 'Adds configurations for templates to format names of mvv objects.';
    }

    protected function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_MODUL', 'string', 'global', '{{module_name}} ({{semester_validity}})', 'mvv',
                 'Template for modules. Possible placeholders: {{module_code}}, {{module_name}}, {{semester_validity}}',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_MODULTEIL', 'string', 'global', '', 'mvv',
                 'Template for module parts. Possible placeholders: "
                    . "{{part_number}}, {{part_number_label}}, {{part_name}}, {{teaching_method}}. "
                    . "If empty a default name will be displayed.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_STGTEILVERSION', 'string', 'global', '{{subject_name}} {{credit_points CP}} "
                    . "{{purpose_addition}}{{, version_ordinal_number}} {{version_type}} {{semester_validity}}', 'mvv',
                 'Template for versions of study courses. Possible placeholders: "
                    . "{{subject_name}}, {{credit_points}}, {{purpose_addition}}, {{version_number}}, {{version_type}}, "
                    . "{{version_ordinal_number}}, {{semester_validity}}.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_STGTEILABSCHNITTMODUL', 'string', 'global', '{{module_code}} - {{module_name}} ({{semester_validity}})', 'mvv',
                 'Template for modules displayed in the context of a study course. Possible placeholders: "
                    . "{{module_code}}, {{module_name}}, {{semester_validity}}. If empty a default name will be displayed.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_STUDIENGANG', 'string', 'global', '{{study_course_name}} ({{degree_category}})', 'mvv',
                 'Template for the name of a study course. Possible placeholders: "
                    . "{{study_course_name}}, {{degree_name}}, {{degree_category}}.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_STUDIENGANGTEIL', 'string', 'global', '{{subject_name}} {{credit_points}} CP {{purpose_addition}}', 'mvv',
                 'Template for parts of a study course. Possible placeholders: "
                    . "{{subject_name}}, {{credit_points}}, {{purpose_addition}}.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_FACHBEREICH', 'string', 'global', '{{faculty_short_name}} - {{name}}', 'mvv',
                 'Template for departments. Possible placeholders: {{department_name}}, {{faculty_short_name}}. "
                    . "Used only if the department is not a faculty. If empty the name of the institution will be displayed.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
        $db->exec(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'MVV_TEMPLATE_NAME_ABSCHLUSS', 'string', 'global', '', 'mvv',
                 'Template for degrees. Possible placeholders: {{degree_name}}, {{degree_short_name}}. "
                    . "If empty a default name will be displayed.',
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )"
        );
    }

    protected function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'MVV_TEMPLATE_NAME_MODUL',
                      'MVV_TEMPLATE_NAME_MODULTEIL',
                      'MVV_TEMPLATE_NAME_STGTEILABSCHNITTMODUL',
                      'MVV_TEMPLATE_NAME_STUDIENGANG',
                      'MVV_TEMPLATE_NAME_STUDIENGANGTEIL',
                      'MVV_TEMPLATE_NAME_FACHBEREICH',
                      'MVV_TEMPLATE_NAME_ABSCHLUSS'
                  )";
        DBManger::get()->exec($query);
    }
}
