<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/1998
 */
final class AlterAuthUserMd5NullColumns extends Migration
{
    public function description()
    {
        return 'Removes NULL values from columns in auth_user_md5';
    }

    public function up()
    {
        $query = "UPDATE `auth_user_md5` SET `Vorname` = '' WHERE `Vorname` IS NULL";
        DBManager::get()->exec($query);

        $query = "UPDATE `auth_user_md5` SET `Nachname` = '' WHERE `Nachname` IS NULL";
        DBManager::get()->exec($query);

        $query = "UPDATE `auth_user_md5` SET `Email` = '' WHERE `Email` IS NULL";
        DBManager::get()->exec($query);

        $query = "ALTER TABLE `auth_user_md5`
                  CHANGE COLUMN `Vorname` `Vorname` VARCHAR(64) NOT NULL DEFAULT '',
                  CHANGE COLUMN `Nachname` `Nachname` VARCHAR(64) NOT NULL DEFAULT '',
                  CHANGE COLUMN `Email` `Email` VARCHAR(256) NOT NULL DEFAULT ''";
        DBManager::get()->exec($query);
    }

    public function down()
    {
        $query = "ALTER TABLE `auth_user_md5`
                  CHANGE COLUMN `Vorname` `Vorname` VARCHAR(64) DEFAULT NULL,
                  CHANGE COLUMN `Nachname` `Nachname` VARCHAR(64) DEFAULT NULL,
                  CHANGE COLUMN `Email` `Email` VARCHAR(256) DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
