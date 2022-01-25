<?php
class tic409 extends Migration
{
    public function description()
    {
        return "create BannerRoles table";
    }

    public function up()
    {
        $query = 'CREATE TABLE IF NOT EXISTS `banner_roles` (
          `ad_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
          `roleid` int(10) NOT NULL,
           PRIMARY KEY (`ad_id`, `roleid`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC';

        DBManager::get()->exec($query);

        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'BANNER_ONLY_SYSTEM_ROLES',
            ':description' => 'Über diese Option wird die Auswahl der rollenspezifischen Banner auf Systemrollen begrenzt',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);
    }

    public function down()
    {
        DBManager::get()->exec('DROP TABLE IF EXISTS `banner_roles`');
    }
}
