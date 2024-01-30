<?php


class AlterCalendarTables extends Migration
{
    public function description()
    {
        return 'Alters the tables for the personal calendar and related tables.';
    }


    protected function migrateEventData()
    {
        $db = DBManager::get();

        $db->exec("RENAME TABLE `event_data` TO calendar_dates");

        //Move the content of the "day" column into the "offset" column
        //which is still called "sinterval" at this point:
        $db->exec(
            "UPDATE `calendar_dates`
            SET `sinterval` = `day`
            WHERE `day` <> ''"
        );

        $db->exec(
            "ALTER TABLE `calendar_dates`
            DROP COLUMN `ts`,
            DROP COLUMN `duration`,
            DROP COLUMN `priority`,
            DROP COLUMN `day`,
            CHANGE COLUMN event_id id CHAR(32) COLLATE latin1_bin NOT NULL,
            CHANGE COLUMN uid unique_id VARCHAR(255) UNIQUE NOT NULL,
            CHANGE COLUMN start begin INT(11) NOT NULL DEFAULT 0,
            CHANGE COLUMN end end INT(11) NOT NULL DEFAULT 0,
            CHANGE COLUMN summary title VARCHAR(255) NOT NULL DEFAULT '',
            CHANGE COLUMN class access ENUM('PUBLIC', 'PRIVATE', 'CONFIDENTIAL') COLLATE latin1_bin NOT NULL DEFAULT 'PRIVATE',
            CHANGE COLUMN categories user_category VARCHAR(64) NULL DEFAULT '',
            CHANGE COLUMN category_intern category TINYINT(3) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN location location VARCHAR(255) NULL DEFAULT '',
            CHANGE COLUMN linterval `interval` TINYINT(2) NULL DEFAULT 0,
            CHANGE COLUMN sinterval `offset` TINYINT(2) NULL DEFAULT 0,
            CHANGE COLUMN wdays days VARCHAR(7) NULL DEFAULT '',
            CHANGE COLUMN rtype repetition_type ENUM('SINGLE', 'DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY') DEFAULT 'SINGLE',
            CHANGE COLUMN `count` number_of_dates SMALLINT(5) UNSIGNED NOT NULL DEFAULT '1',
            CHANGE COLUMN `expire` repetition_end BIGINT(10) NOT NULL DEFAULT '0',
            CHANGE COLUMN mkdate mkdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN chdate chdate INT(11) UNSIGNED NOT NULL DEFAULT 0,
            CHANGE COLUMN importdate import_date INT(11) NOT NULL DEFAULT 0"
        );

        $get_stmt = $db->prepare("SELECT `id`, `exceptions` FROM `calendar_dates`");
        $exception_stmt = $db->prepare(
            "INSERT INTO `calendar_date_exceptions`
            (`calendar_date_id`, `date`, `mkdate`, `chdate`)
            VALUES
            (:calendar_date_id, :date, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())"
        );
        $get_stmt->execute();
        while ($row = $get_stmt->fetch()) {
            //Migrate exceptions:
            $exceptions = explode(',', $row['exceptions'] ?? '');
            foreach ($exceptions as $exception) {
                $exception_stmt->execute([
                    'calendar_date_id' => $row['id'],
                    'date' => date('Y-m-d', intval(trim($exception)))
                ]);
            }
        }

        $db->exec(
            "ALTER TABLE `calendar_dates` DROP COLUMN `exceptions`"
        );
    }


    protected function migrateCalendarEvent()
    {
        $db = DBManager::get();

        $db->exec(
            "RENAME TABLE `calendar_event` TO calendar_date_assignments"
        );

        $db->exec(
            "ALTER TABLE `calendar_date_assignments`
            ADD COLUMN participation ENUM('', 'ACCEPTED', 'DECLINED', 'ACKNOWLEDGED') COLLATE latin1_bin NOT NULL DEFAULT '',
            CHANGE COLUMN event_id calendar_date_id CHAR(32) COLLATE latin1_bin NOT NULL,
            CHANGE COLUMN group_status old_group_status TINYINT(1) UNSIGNED NOT NULL DEFAULT '0',
            CHANGE COLUMN mkdate mkdate INT(11) NOT NULL DEFAULT 0,
            CHANGE COLUMN chdate chdate INT(11) NOT NULL DEFAULT 0"
        );

        $db->exec(
            "UPDATE `calendar_date_assignments`
            SET `participation` = IF (
                `old_group_status` = '2',
                'ACCEPTED',
                IF (`old_group_status` = '3',
                    'DECLINED',
                    IF (`old_group_status` = '4',
                        'ACKNOWLEDGED',
                        ''
                        )
                    )
                )"
        );

        $db->exec("ALTER TABLE `calendar_date_assignments` DROP COLUMN `old_group_status`");
    }


    protected function migrateCalendarUser()
    {
        //All entries from calendar_user are transferred to the contacts table
        //which gets an extra column so that it can store the calendar access level.
        $db = DBManager::get();

        $db->exec(
            "ALTER TABLE `contact`
            CHANGE COLUMN mkdate mkdate INT(11) NOT NULL DEFAULT 0,
            ADD COLUMN chdate INT(11) NOT NULL DEFAULT 0,
            ADD COLUMN calendar_permissions ENUM('', 'READ', 'WRITE') COLLATE latin1_bin NOT NULL DEFAULT ''"
        );

        $db->exec(
            "INSERT INTO `contact`
            (`owner_id`, `user_id`, `calendar_permissions`, `mkdate`, `chdate`)
            SELECT `owner_id`, `user_id`,
                    IF(`permission` = '4', 'WRITE', IF(`permission` = '2', 'READ', '')) AS calendar_permissions,
                   `mkdate`, `chdate`
                   FROM `calendar_user`
            ON DUPLICATE KEY UPDATE `calendar_permissions` = calendar_permissions"
        );

        $db->exec("DROP TABLE `calendar_user`");
    }


    protected function addContactGroups()
    {
        $db = DBManager::get();

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `contact_groups` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `owner_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `old_group_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `chdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY(`id`)
            )"
        );
        $db->exec(
            "CREATE TABLE IF NOT EXISTS `contact_group_items` (
                `group_id` BIGINT UNSIGNED NOT NULL,
                `user_id` CHAR(32) COLLATE latin1_bin NOT NULL,
                `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                `chdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
                 PRIMARY KEY(`group_id`, `user_id`)
            )"
        );

        //Migrate entries from statusgruppen and statusgruppe_user:
        $old_groups = $db->query(
            "SELECT `statusgruppe_id`, `name`, `range_id`, `mkdate`, `chdate`
            FROM `statusgruppen`
            WHERE `range_id` IN (
                SELECT `user_id` FROM `auth_user_md5`
            )"
        )->fetchAll(PDO::FETCH_ASSOC);

        $new_group_stmt = $db->prepare(
            "INSERT INTO `contact_groups`
            (`name`, `owner_id`, `old_group_id`, `mkdate`, `chdate`)
            VALUES (:name, :user_id, :old_group_id, :mkdate, :chdate)"
        );

        $group_member_stmt = $db->prepare(
            "INSERT INTO `contact_group_items`
            (`group_id`, `user_id`, `mkdate`, `chdate`)
            SELECT `contact_groups`.`id` AS group_id, `user_id`, `statusgruppe_user`.`mkdate` as mkdate, `statusgruppe_user`.`mkdate` AS chdate
            FROM `statusgruppe_user`
            INNER JOIN `contact_groups`
            ON `statusgruppe_user`.`statusgruppe_id` = `contact_groups`.`old_group_id`
            WHERE `statusgruppe_id` = :old_group_id"
        );
        $old_member_delete_stmt = $db->prepare("DELETE FROM `statusgruppe_user` WHERE `statusgruppe_id` = :old_group_id");

        foreach ($old_groups as $old_group) {
            $new_group_stmt->execute([
                'name'         => $old_group['name'],
                'user_id'      => $old_group['range_id'],
                'old_group_id' => $old_group['statusgruppe_id'],
                'mkdate'       => $old_group['mkdate'],
                'chdate'       => $old_group['chdate']
            ]);
            $group_member_stmt->execute([
                'old_group_id' => $old_group['statusgruppe_id']
            ]);
            $old_member_delete_stmt->execute([
                'old_group_id' => $old_group['statusgruppe_id']
            ]);
        }

        //Delete old status groups:
        $db->exec(
            "DELETE FROM `statusgruppen` WHERE `range_id` IN (
                SELECT `user_id` FROM `auth_user_md5`
            )"
        );

        //Delete the old group ID:
        $db->exec("ALTER TABLE `contact_groups` DROP COLUMN `old_group_id`");
    }

    protected function up()
    {
        $db = DBManager::get();

        $db->exec(
            "CREATE TABLE IF NOT EXISTS `calendar_date_exceptions` (
            `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            `calendar_date_id` CHAR(32) COLLATE latin1_bin NOT NULL,
            `date` DATE NOT NULL,
            `mkdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `chdate` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
            )"
        );

        $this->migrateEventData();

        $this->migrateCalendarEvent();

        $this->migrateCalendarUser();

        $this->addContactGroups();
    }


    protected function down()
    {
        //I see nothing, I hear nothing, I know nothing! NOTHING!!
    }
}
