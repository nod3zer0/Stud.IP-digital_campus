<?php

class AddOersi extends Migration
{
    public function description ()
    {
        return 'Adds the OER-search-index OERSI to OER Campus.';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `oer_hosts`
            ADD COLUMN `sorm_class` varchar(50) DEFAULT 'OERHost' NOT NULL AFTER `host_id`
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO `oer_hosts`
            SET `host_id` = MD5('oersi'),
                `name` = 'OERSI',
                `sorm_class` = 'OERHostOERSI',
                `url` = 'https://oersi.de',
                `public_key` = '',
                `private_key` = '',
                `active` = '1',
                `index_server` = '1',
                `allowed_as_index_server` = '1',
                `last_updated` = UNIX_TIMESTAMP(),
                `chdate` = UNIX_TIMESTAMP(),
                `mkdate` = UNIX_TIMESTAMP()
        ");
        DBManager::get()->exec("
            ALTER TABLE `oer_material`
            ADD COLUMN `source_url` varchar(256) DEFAULT NULL AFTER `published_id_on_twillo`,
            ADD COLUMN `data` TEXT DEFAULT NULL AFTER `source_url`
        ");
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('OER_OERSI_ONLY_DOWNLOADABLE', '1', 'boolean', 'global',
            'OERCampus', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Should the search in OERSI only find downloadable OERs?')"
        );
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `oer_hosts`
            DROP COLUMN `sorm_class`
        ");
        DBManager::get()->exec("
            DELETE FROM `oer_hosts`
            WHERE `host_id` = MD5('oersi')
        ");
        DBManager::get()->exec("
            ALTER TABLE `oer_material`
            DROP COLUMN `source_url`,
            DROP COLUMN `data`
        ");
        DBManager::get()->exec(
            "DELETE FROM `config`
            WHERE `field` = 'OER_OERSI_ONLY_DOWNLOADABLE'"
        );
        DBManager::get()->exec(
            "DELETE FROM `config_values`
            WHERE `field` = 'OER_OERSI_ONLY_DOWNLOADABLE'"
        );
    }
}
