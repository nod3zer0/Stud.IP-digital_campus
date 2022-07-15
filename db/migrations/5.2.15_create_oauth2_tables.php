<?php

class CreateOauth2Tables extends Migration
{
    public function description()
    {
        return 'creates all necessary tables for the OAuth2 plugin';
    }

    public function up()
    {
        $db = DBManager::get();

        $query = "CREATE TABLE IF NOT EXISTS `oauth2_access_tokens` (
                    `id`         VARCHAR(100) NOT NULL,
                    `user_id`    CHAR(32) COLLATE `latin1_bin` NULL,
                    `client_id`  BIGINT UNSIGNED NOT NULL,
                    `scopes`     TEXT NULL,
                    `revoked`    TINYINT(1) NOT NULL DEFAULT 0,
                    `expires_at` INT(11) NULL,
                    `mkdate`     INT(11) NOT NULL,
                    `chdate`     INT(11) NOT NULL,

                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`)
                  )";
        $db->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `oauth2_auth_codes` (
                    `id`           VARCHAR(100) NOT NULL,
                    `user_id`      CHAR(32) COLLATE `latin1_bin` NOT NULL,
                    `client_id`    BIGINT UNSIGNED NOT NULL,
                    `scopes`       TEXT NULL,
                    `revoked`      TINYINT(1) NOT NULL DEFAULT 0,
                    `expires_at`   INT(11) NULL,
                    `mkdate`       INT(11) NOT NULL,
                    `chdate`       INT(11) NOT NULL,

                    PRIMARY KEY (`id`),
                    KEY `user_id` (`user_id`)
                  )";
        $db->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `oauth2_clients` (
                    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `name`          VARCHAR(255) NOT NULL,
                    `secret`        VARCHAR(100) NULL,
                    `redirect`      TEXT NOT NULL,
                    `revoked`       TINYINT(1) NOT NULL DEFAULT 0,

                    `description`   TEXT NULL,
                    `owner`         VARCHAR(255) NULL,
                    `homepage`      VARCHAR(255) NULL,
                    `admin_notes`   TEXT NULL,

                    `mkdate`        INT(11) NOT NULL,
                    `chdate`        INT(11) NOT NULL,

                    PRIMARY KEY (`id`)
                  )";
        $db->exec($query);

        $query = "CREATE TABLE IF NOT EXISTS `oauth2_refresh_tokens` (
                    `id`              VARCHAR(100) NOT NULL,
                    `access_token_id` VARCHAR(100) NOT NULL,
                    `revoked`         TINYINT(1) NOT NULL DEFAULT 0,
                    `expires_at`      INT(11) NULL,

                    PRIMARY KEY (`id`),
                    KEY `access_token_id` (`access_token_id`)
                  )";
        $db->exec($query);
    }

    public function down()
    {
        $db = \DBManager::get();
        $db->exec('DROP TABLE IF EXISTS `oauth2_access_tokens`');
        $db->exec('DROP TABLE IF EXISTS `oauth2_auth_codes`');
        $db->exec('DROP TABLE IF EXISTS `oauth2_clients`');
        $db->exec('DROP TABLE IF EXISTS `oauth2_refresh_tokens`');
    }
}
