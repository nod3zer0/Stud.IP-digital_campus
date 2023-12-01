<?php

final class ExtendCwCertificates extends Migration
{
    use DatabaseMigrationTrait;

    public function description()
    {
        return 'Provide global config entry for Courseware certificates and add a fileref_id to the ' .
            'cw_certificates table to track which certificate was generated';
    }

    protected function up()
    {
        // Create global config entry for (de-)activating Courseware certificate and reminder functionality.
        DBManager::get()->execute("INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
            VALUES
            (:field, :value, :type, 'global', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)",
            [
                'field' => 'COURSEWARE_CERTIFICATES_ENABLE',
                'value' => 1,
                'type' => 'boolean',
                'description' => 'Schaltet Courseware-Zertifikate, -Erinnerungen und -Fortschrittsrücksetzung ein oder aus'
            ]
        );

        if (!$this->columnExists('cw_certificates', 'fileref_id')) {
            DBManager::get()->execute(
                "ALTER TABLE `cw_certificates` ADD `fileref_id` CHAR(32) NULL DEFAULT NULL COLLATE latin1_bin AFTER `unit_id`"
            );
        }
    }

    protected function down()
    {
        if ($this->columnExists('cw_certificates', 'fileref_id')) {
            DBManager::get()->execute("ALTER TABLE `cw_certificates` DROP `fileref_id`");
        }

        DBManager::get()->execute("DELETE FROM `config_values` WHERE `field` = :field",
            ['field' => 'COURSEWARE_CERTIFICATES_ENABLE']);
        DBManager::get()->execute("DELETE FROM `config` WHERE `field` = :field",
            ['field' => 'COURSEWARE_CERTIFICATES_ENABLE']);
    }
}
