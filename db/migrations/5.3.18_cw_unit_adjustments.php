<?php

class CwUnitAdjustments extends Migration
{
    public function description()
    {
        return 'adjust courseware config to units';
    }

    public function up()
    {
        // Add column for storing per-unit configuration.
        DBManager::get()->exec(
            "ALTER TABLE `cw_units` ADD `config` TEXT NOT NULL DEFAULT '' AFTER `withdraw_date`"
        );

        // Which fields in config are relevant for this migration?
        $fields = [
            'COURSEWARE_SEQUENTIAL_PROGRESSION',
            'COURSEWARE_EDITING_PERMISSION',
            'COURSEWARE_CERTIFICATE_SETTINGS',
            'COURSEWARE_REMINDER_SETTINGS',
            'COURSEWARE_RESET_PROGRESS_SETTINGS',
            'COURSEWARE_LAST_REMINDER',
            'COURSEWARE_LAST_PROGRESS_RESET'
        ];

        // Which courses do have custom courseware settings and need to be migrated?
        $ranges = DBManager::get()->fetchFirst(
            "SELECT DISTINCT `range_id` FROM `config_values` WHERE `field` IN (:fields)",
            ['fields' => $fields]
        );

        $update = DBManager::get()->prepare("UPDATE `cw_units` SET `config` = :config WHERE `id` = :unit");

        // Get courseware settings per course as stored in config_values,
        foreach ($ranges as $course) {
            $global = DBManager::get()->fetchAll(
                "SELECT `field`, `value` FROM `config_values` WHERE `range_id` = :range AND `field` IN (:fields)",
                ['range' => $course, 'fields' => $fields]
            );

            // Build configuration per unit.
            $config = [];
            // Convert values.
            foreach ($global as $one) {

                $decoded = json_decode($one['value'], true);

                foreach ($decoded as $unit_id => $settings) {
                    switch ($one['field']) {
                        case 'COURSEWARE_SEQUENTIAL_PROGRESSION':
                            $config[$unit_id]['sequential_progression'] = $settings;
                            break;
                        case 'COURSEWARE_EDITING_PERMISSION':
                            $config[$unit_id]['editing_permission'] = $settings;
                            break;
                        case 'COURSEWARE_CERTIFICATE_SETTINGS':
                            $config[$unit_id]['certificate'] = $settings;
                            break;
                        case 'COURSEWARE_REMINDER_SETTINGS':
                            $config[$unit_id]['reminder'] = $settings;
                            break;
                        case 'COURSEWARE_RESET_PROGRESS_SETTINGS':
                            $config[$unit_id]['reset_progress'] = $settings;
                            break;
                        case 'COURSEWARE_LAST_REMINDER':
                            $config[$unit_id]['last_reminder'] = $settings;
                            break;
                        case 'COURSEWARE_LAST_PROGRESS_RESET':
                            $config[$unit_id]['last_progress_reset'] = $settings;
                            break;
                    }
                }
            }

            // Now write per-unit configurations to database.
            foreach ($config as $unit => $config) {
                $update->execute(['config' => json_encode($config), 'unit' => $unit]);
            }

        }

        // Drop old values from global config.
        DBManager::get()->execute(
            "DELETE FROM `config` WHERE `field` IN (:fields)",
            ['fields' => $fields]
        );
        DBManager::get()->execute(
            "DELETE FROM `config_values` WHERE `field` IN (:fields)",
            ['fields' => $fields]
        );

        // Add column for storing unit_id with certificate date.
        DBManager::get()->exec(
            "ALTER TABLE `cw_certificates` ADD `unit_id` INT NOT NULL AFTER `course_id`"
        );
        DBManager::get()->exec("ALTER TABLE `cw_certificates` DROP INDEX `index_course_id`, DROP INDEX `index_user_ourse`");
        DBManager::get()->exec("ALTER TABLE `cw_certificates` ADD INDEX index_unit_id (`unit_id`)");
    }

    public function down()
    {
        // Drop columns for storing per-unit configuration.
        DBManager::get()->exec("ALTER TABLE `cw_units` DROP `config`");
        DBManager::get()->exec("ALTER TABLE `cw_certificates` DROP `unit_id`");
    }
}
