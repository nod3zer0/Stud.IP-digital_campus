<?

final class ModernizeWiki extends Migration
{

    public function description()
    {
        return 'The wiki is getting better and mightier.';
    }

    protected function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `wiki_pages` (
                `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `name` varchar(255) NOT NULL,
                `content` mediumtext DEFAULT NULL,
                `parent_id` int(11) DEFAULT NULL,
                `read_permission` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'all',
                `write_permission` varchar(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT 'all',
                `user_id` char(32) NOT NULL,
                `locked_since` bigint(20) DEFAULT NULL,
                `locked_by_user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`page_id`),
                KEY `read_permission` (`read_permission`),
                KEY `write_permission` (`write_permission`),
                KEY `range_id` (`range_id`)
            )
        ");
        DBManager::get()->exec("
            CREATE TABLE `wiki_versions` (
                `version_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `page_id` int(11) unsigned NOT NULL,
                `name` varchar(128) NOT NULL,
                `content` text DEFAULT NULL,
                `user_id` char(32) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`version_id`),
                KEY `page_id` (`page_id`),
                KEY `mkdate` (`mkdate`)
            )
        ");

        DBManager::get()->exec("
            INSERT INTO `wiki_pages` (`range_id`, `name`, `content`, `parent_id`, `read_permission`, `write_permission`, `user_id`, `chdate`, `mkdate`)
            SELECT `wiki`.`range_id`,
                `wiki`.`keyword`,
                `wiki`.`body`,
                NULL,
                IF(`wiki_page_config`.`read_restricted` > 0, 'tutor', 'all'),
                IF(`wiki_page_config`.`edit_restricted` > 0, 'tutor', 'all'),
                `wiki`.`user_id`,
                `wiki`.`chdate`,
                IFNULL(`wiki`.`mkdate`, UNIX_TIMESTAMP())
            FROM `wiki`
            INNER JOIN (
                SELECT `wiki`.`range_id`, `wiki`.`keyword`, MAX(`version`) AS `version`
                FROM `wiki`
                GROUP BY `wiki`.`range_id`, `wiki`.`keyword`
            ) AS `wiki_grouped` ON (`wiki_grouped`.`range_id` = `wiki`.`range_id` AND `wiki_grouped`.`keyword` = `wiki`.`keyword` AND `wiki_grouped`.`version` = `wiki`.`version`)
            LEFT JOIN `wiki_page_config` ON (`wiki`.`keyword` = `wiki_page_config`.`keyword` AND `wiki_page_config`.`range_id` = `wiki_grouped`.`range_id`)
        ");
        DBManager::get()->exec("
            UPDATE `wiki_pages`
            SET `parent_id` = (
                SELECT `wp`.`page_id`
                FROM (SELECT * FROM `wiki_pages`) AS `wp`
                    INNER JOIN `wiki` ON (`wiki`.`range_id` = `wp`.`range_id` AND `wiki`.`keyword` = `wp`.`name`)
                WHERE `wiki`.`ancestor` = `wiki_pages`.`name`
                    AND `wp`.`range_id` = `wiki_pages`.`range_id`
                LIMIT 1
            )
        ");
        DBManager::get()->exec("
            INSERT INTO `wiki_versions` (`page_id`, `name`, `content`, `user_id`, `mkdate`)
            SELECT `wiki_pages`.`page_id`,
                   `wiki`.`keyword`,
                   `wiki`.`body`,
                   `wiki`.`user_id`,
                   `wiki`.`mkdate`
            FROM `wiki`
                LEFT JOIN (
                    SELECT `wiki`.`range_id`, `keyword`, MAX(`version`) AS `version`
                    FROM `wiki`
                    GROUP BY `wiki`.`range_id`, `wiki`.`keyword`
                ) AS `wiki_grouped` ON (`wiki`.`range_id` = `wiki_grouped`.`range_id` AND `wiki`.`keyword` = `wiki_grouped`.`keyword`)
                INNER JOIN `wiki_pages` ON (`wiki_pages`.`name` = `wiki`.`keyword` AND `wiki_pages`.`range_id` = `wiki`.`range_id`)
            WHERE `wiki`.`version` != `wiki_grouped`.`version`
        ");

        //first delete all orphaned entries:
        DBManager::get()->exec("
            DELETE FROM `wiki_links`
            WHERE `from_keyword` NOT IN (SELECT `name` FROM `wiki_pages` WHERE `wiki_links`.`range_id` = `wiki_pages`.`range_id`)
                OR `to_keyword` NOT IN (SELECT `name` FROM `wiki_pages` WHERE `wiki_links`.`range_id` = `wiki_pages`.`range_id`)
        ");
        DBManager::get()->exec("
            UPDATE `wiki_links`
            SET `from_keyword` = (SELECT `page_id` FROM `wiki_pages` WHERE `wiki_pages`.`name` = `wiki_links`.`from_keyword` AND `wiki_links`.`range_id` = `wiki_pages`.`range_id` LIMIT 1),
                `to_keyword` = (SELECT `page_id` FROM `wiki_pages` WHERE `wiki_pages`.`name` = `wiki_links`.`to_keyword` AND `wiki_links`.`range_id` = `wiki_pages`.`range_id` LIMIT 1)
        ");
        DBManager::get()->exec("
            ALTER TABLE `wiki_links`
            CHANGE `from_keyword` `from_page_id` int(11) unsigned NOT NULL,
            CHANGE `to_keyword` `to_page_id` int(11) unsigned NOT NULL,
            CHANGE `range_id` `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''
        ");

        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO config (field, value, type, `range`, mkdate, chdate, description)
            VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)
        ");
        $statement->execute([
            'name'        => 'WIKI_STARTPAGE_ID',
            'description' => 'ID der Wiki-Startseite des Wikis.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => ''
        ]);
        $statement->execute([
            'name'        => 'WIKI_CREATE_PERMISSION',
            'description' => 'Status, den es braucht, um neue Wiki-Seiten anzulegen.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'all'
        ]);
        $statement->execute([
            'name'        => 'WIKI_RENAME_PERMISSION',
            'description' => 'Status, den es braucht, um Wiki-Seiten umzubenennen.',
            'range'       => 'course',
            'type'        => 'string',
            'value'       => 'all'
        ]);

        DBManager::get()->exec("
            INSERT INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`, `comment`)
            SELECT 'WIKI_STARTPAGE_ID', `wiki_pages`.`range_id`, `wiki_pages`.`page_id`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), ''
            FROM `wiki`
                INNER JOIN `wiki_pages` ON (`wiki_pages`.`name` = `wiki`.`keyword` AND `wiki_pages`.`range_id` = `wiki`.`range_id`)
            WHERE `keyword` = 'WikiWikiWeb'
            GROUP BY `wiki`.`range_id`
        ");
        DBManager::get()->exec("
            CREATE TABLE `wiki_online_editing_users` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `page_id` int(11) NOT NULL,
                `editing` tinyint(1) NOT NULL DEFAULT 0,
                `editing_request` tinyint(1) NOT NULL DEFAULT 0,
                `chdate` int(11) NOT NULL,
                `mkdate` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `user_id_2` (`user_id`,`page_id`),
                KEY `user_id` (`user_id`),
                KEY `page_id` (`page_id`),
                KEY `chdate` (`chdate`)
            )
        ");

        //if SuperWiki installed
        $superwiki_enabled = (bool) DBManager::get()->fetchColumn("SELECT 1 FROM `plugins` WHERE `pluginclassname` = 'SuperWiki' AND `enabled` = 'yes'");
        if ($superwiki_enabled && !$GLOBALS['PREVENT_MIGRATE_SUPERWIKI']) {
            DBManager::get()->exec("
                INSERT INTO `wiki_pages` (`range_id`, `name`, `content`, `parent_id`, `read_permission`, `write_permission`, `user_id`, `chdate`, `mkdate`)
                SELECT `superwiki_pages`.`seminar_id`,
                    `superwiki_pages`.`name`,
                    `superwiki_pages`.`content`,
                    NULL,
                    `superwiki_pages`.`read_permission`,
                    `superwiki_pages`.`write_permission`,
                    `superwiki_pages`.`last_author`,
                    `superwiki_pages`.`chdate`,
                    `superwiki_pages`.`mkdate`
                FROM `superwiki_pages`
            ");
            DBManager::get()->exec("
                INSERT INTO `wiki_versions` (`page_id`, `name`, `content`, `user_id`, `mkdate`)
                SELECT `wiki_pages`.`page_id`,
                       `superwiki_versions`.`name`,
                       `superwiki_versions`.`content`,
                       `superwiki_versions`.`last_author`,
                       `superwiki_versions`.`chdate`
                FROM `superwiki_versions`
                    INNER JOIN `superwiki_pages` ON (`superwiki_pages`.`page_id` = `superwiki_versions`.`page_id`)
                    INNER JOIN `wiki_pages` ON (`wiki_pages`.`range_id` = `superwiki_pages`.`seminar_id` AND `wiki_pages`.`name` = `superwiki_pages`.`name`)
            ");
        }
        DBManager::get()->exec("
            DROP TABLE `wiki`
        ");
        DBManager::get()->exec("
            DROP TABLE `wiki_page_config`
        ");
        DBManager::get()->exec("
            DROP TABLE `wiki_locks`
        ");
    }

    protected function down()
    {
        DBManager::get()->exec("
            DROP TABLE `wiki_online_editing_users`
        ");
        DBManager::get()->exec("
            CREATE TABLE `wiki_locks` (
                `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                `chdate` int(10) unsigned NOT NULL DEFAULT 0,
                PRIMARY KEY (`range_id`,`user_id`,`keyword`),
                KEY `user_id` (`user_id`),
                KEY `chdate` (`chdate`)
            )
        ");
        DBManager::get()->exec("
            ALTER TABLE `wiki_links`
            CHANGE `from_page_id` `from_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
            CHANGE `to_page_id` `to_keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
            CHANGE `range_id` `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT ''
        ");
        DBManager::get()->exec("
            UPDATE `wiki_links`
            SET `from_keyword` = (SELECT `name` FROM `wiki_pages` WHERE `wiki_pages`.`page_id` = `wiki_links`.`from_keyword` AND `wiki_links`.`range_id` = `wiki_pages`.`range_id` LIMIT 1),
                `to_keyword` = (SELECT `name` FROM `wiki_pages` WHERE `wiki_pages`.`page_id` = `wiki_links`.`to_keyword` AND `wiki_links`.`range_id` = `wiki_pages`.`range_id` LIMIT 1)
        ");

        DBManager::get()->exec("
            CREATE TABLE `wiki` (
                `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL DEFAULT '',
                `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin DEFAULT NULL,
                `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
                `body` mediumtext NOT NULL,
                `ancestor` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
                `chdate` int(10) unsigned DEFAULT NULL,
                `version` int(11) NOT NULL DEFAULT 0,
                `mkdate` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`range_id`,`keyword`,`version`),
                KEY `user_id` (`user_id`),
                KEY `chdate` (`chdate`)
            )
        ");

        DBManager::get()->exec("
            INSERT INTO `wiki` (`range_id`, `user_id`, `keyword`, `body`, `ancestor`, `chdate`, `version`, `mkdate`)
            SELECT `wiki_pages`.`range_id`, `wiki_pages`.`last_author`, `wiki_pages`.`name`, `wiki_pages`.`content`, `wp2`.`name`, `wiki_pages`.`chdate`, COUNT(`wiki_versions`.`page_id`) + 1, `wiki_pages`.`mkdate`
            FROM `wiki_pages`
                LEFT JOIN `wiki_pages` AS wp2 ON (`wiki_pages`.`parent_id` = `wp2`.`page_id`)
                LEFT JOIN `wiki_versions` ON (`wiki_versions`.`page_id` = `wiki_pages`.`page_id`)
            GROUP BY `wiki_pages`.`page_id`
        ");
        DBManager::get()->exec("
            INSERT INTO `wiki` (`range_id`, `user_id`, `keyword`, `body`, `ancestor`, `chdate`, `version`, `mkdate`)
            SELECT `wiki_pages`.`range_id`, `wiki_versions`.`user_id`, `wiki_pages`.`name`, `wiki_versions`.`content`, `wp2`.`name`, `wiki_versions`.`chdate`, 1, `wiki_pages`.`mkdate`
            FROM `wiki_versions`
                LEFT JOIN `wiki_pages` ON (`wiki_pages`.`page_id` = `wiki_versions`.`page_id`)
                LEFT JOIN `wiki_pages` AS `wp2` ON (`wp2`.`page_id` = `wiki_pages`.`parent_id`)
            ORDER BY `wiki_versions`.`mkdate`
        ");

        DBManager::get()->exec("
            CREATE TABLE `wiki_page_config` (
                `range_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
                `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
                `read_restricted` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `edit_restricted` tinyint(3) unsigned NOT NULL DEFAULT 0,
                `mkdate` int(10) unsigned DEFAULT NULL,
                `chdate` int(10) unsigned DEFAULT NULL,
                PRIMARY KEY (`range_id`,`keyword`)
            )
        ");
        DBManager::get()->exec("
            INSERT INTO `wiki_page_config` (`range_id`, `keyword`, `read_restricted`, `edit_restricted`, `chdate`, `mkdate`)
            SELECT `wiki_pages`.`range_id`, `wiki_pages`.`name`, IF(`wiki_pages`.`read_permission` = 'all', 0, 1), IF(`wiki_pages`.`write_permission` = 'all', 0, 1), `wiki_pages`.`chdate`, `wiki_pages`.`mkdate`
            FROM `wiki_pages`
        ");

        DBManager::get()->exec("
            DROP TABLE `wiki_pages`
        ");
        DBManager::get()->exec("
            DROP TABLE `wiki_versions`
        ");
        DBManager::get()->exec("
            DROP TABLE `wiki_settings`
        ");

        DBManager::get()->exec("
            DELETE FROM `config_values`
            WHERE `field` = 'WIKI_STARTPAGE_ID'
        ");
        DBManager::get()->exec("
            DELETE FROM `config`
            WHERE `field` = 'WIKI_STARTPAGE_ID'
        ");
        DBManager::get()->exec("
            DELETE FROM `config_values`
            WHERE `field` = 'WIKI_CREATE_PERMISSION'
        ");
        DBManager::get()->exec("
            DELETE FROM `config`
            WHERE `field` = 'WIKI_CREATE_PERMISSION'
        ");
        DBManager::get()->exec("
            DELETE FROM `config_values`
            WHERE `field` = 'WIKI_RENAME_PERMISSION'
        ");
        DBManager::get()->exec("
            DELETE FROM `config`
            WHERE `field` = 'WIKI_RENAME_PERMISSION'
        ");
    }

}
