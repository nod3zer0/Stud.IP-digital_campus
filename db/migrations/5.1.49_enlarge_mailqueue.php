<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/1134
 */
final class EnlargeMailqueue extends Migration
{
    public function description()
    {
        return 'alters mail_queue_entries.mail to MEDIUMTEXT since TEXT is too short';
    }

    protected function up()
    {
        DBManager::get()->exec("ALTER TABLE `mail_queue_entries` MODIFY `mail` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

    protected function down()
    {
        DBManager::get()->exec("ALTER TABLE `mail_queue_entries` MODIFY `mail` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL");
    }

}
