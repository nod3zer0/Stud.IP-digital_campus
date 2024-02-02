<?php

final class Tic3193NoUnlimitedCourses extends Migration
{
    public function description()
    {
        return 'adds option to forbid unlimited courses';
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `sem_classes` ADD `unlimited_forbidden` TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER `is_group`
        ");
        $cache = StudipCacheFactory::getCache();
        $cache->expire('DB_SEM_CLASSES_ARRAY');
    }

    public function down()
    {
        DBManager::get()->exec("ALTER TABLE `sem_classes` DROP `unlimited_forbidden`");
        $cache = StudipCacheFactory::getCache();
        $cache->expire('DB_SEM_CLASSES_ARRAY');
    }
}

