<?php
class tic410 extends Migration
{
    public function description()
    {
        return "create NewsRoles table";
    }

    public function up()
    {
        $db =  DBManager::get();

        $query = 'CREATE TABLE IF NOT EXISTS `news_roles` (
          `news_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
          `roleid` int(10) NOT NULL,
           PRIMARY KEY (`news_id`, `roleid`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;';
        $db->exec($query);

        $query = 'ALTER TABLE `news` ADD COLUMN `prio` tinyint(2) NOT NULL DEFAULT 0 AFTER `allow_comments`';
        $db->exec($query);

        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'NEWS_ONLY_SYSTEM_ROLES',
            ':description' => 'Über diese Option wird die Auswahl der rollenspezifischen Ankündigungen auf Systemrollen begrenzt',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);
    }

    public function down()
    {
        $db =  DBManager::get();

        $db->exec('DROP TABLE IF EXISTS `news_roles`');
        $db->exec('ALTER TABLE `news` DROP COLUMN `prio`');
    }
}
