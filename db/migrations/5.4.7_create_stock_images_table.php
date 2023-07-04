<?php

class CreateStockImagesTable extends Migration
{
    public function description()
    {
        return 'create table for stock images';
    }

    public function up()
    {
        $db = DBManager::get();
        $query =
            "CREATE TABLE IF NOT EXISTS `stock_images` (
             `id` int(11) NOT NULL AUTO_INCREMENT,

             `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
             `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
             `license` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
             `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,

             `mime_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
             `size` int(10) UNSIGNED NOT NULL,
             `width` int(10) UNSIGNED NOT NULL,
             `height` int(10) UNSIGNED NOT NULL,
             `palette` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
             `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,

             `mkdate` int(11) UNSIGNED NOT NULL,
             `chdate` int(11) UNSIGNED NOT NULL,

             PRIMARY KEY (`id`))";
        $db->exec($query);
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec('DROP TABLE IF EXISTS `stock_images`');
    }
}
