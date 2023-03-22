<?php
final class RemoveSmileys extends Migration
{
    protected function up()
    {
        $query = "ALTER TABLE `user_info`
                  DROP COLUMN `smiley_favorite`";
        DBManager::get()->exec($query);

        $query = "DROP TABLE `smiley`";
        DBManager::get()->exec($query);

        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'SMILEYADMIN_ENABLE'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "INSERT INTO `config` (
                      `field`, `value`, `type`, `range`, `section`,
                      `mkdate`, `chdate`,
                      `description`
                  ) VALUES (
                      'SMILEYADMIN_ENABLE', '1', 'boolean', 'global', 'modules', 
                      UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                      'Schaltet ein oder aus, ob die Administration der Smileys verfügbar ist.'
                  )";
        DBManager::get()->exec($query);

        $query = "CREATE TABLE `smiley` (
                    `smiley_id` INT(11) unsigned NOT NULL AUTO_INCREMENT,
                    `smiley_name` VARCHAR(50) DEFAULT NULL,
                    `smiley_width` INT(11) NOT NULL DEFAULT 0,
                    `smiley_height` INT(11) NOT NULL DEFAULT 0,
                    `short_name` VARCHAR(50) DEFAULT NULL,
                    `smiley_counter` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    `short_counter` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    `fav_counter` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                    `mkdate` INT(11) UNSIGNED DEFAULT NULL,
                    `chdate` INT(11) UNSIGNED DEFAULT NULL,
                    PRIMARY KEY (`smiley_id`),
                    UNIQUE KEY `name` (`smiley_name`),
                    KEY `short` (`short_name`)
                  )";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `user_info`
                  ADD COLUMN `smiley_favorite` VARCHAR(255) DEFAULT NULL AFTER `email_forward`";
        DBManager::get()->exec($query);
    }
}
