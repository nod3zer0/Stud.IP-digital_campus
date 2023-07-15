<?

final class TreeChanges extends Migration
{

    const FIELDS = [
        'RANGE_TREE_PERM',
        'SEM_TREE_PERM'
    ];

    public function description()
    {
        return 'Removes old sem_- and range_tree permission settings and institute assignments for sem_tree entries';
    }

    protected function up()
    {
        // Remove config fields for special permissions concerning sem_- and range_tree administration.
        DBManager::get()->execute(
            "DELETE FROM `config_values` WHERE `field` IN (:fields)",
            ['fields' => self::FIELDS]
        );
        DBManager::get()->execute(
            "DELETE FROM `config` WHERE `field` IN (:fields)",
            ['fields' => self::FIELDS]
        );

        // "Transfer" names from assigned institutes to sem_tree entries.
        $stmt = DBManager::get()->prepare("UPDATE `sem_tree` SET `name` = :name WHERE `studip_object_id` = :inst");
        $query = "SELECT DISTINCT `Institut_id`, `Name` FROM `Institute` WHERE `Institut_id` IN (
                SELECT DISTINCT `studip_object_id` FROM  `sem_tree`
            )";
        foreach (DBManager::get()->fetchAll($query) as $institute) {
            $stmt->execute(['name' => $institute['Name'], 'inst' => $institute['Institut_id']]);
        }
        // Remove institute assignments for sem_tree entries.
        DBManager::get()->exec("ALTER TABLE `sem_tree` DROP `studip_object_id`");
    }

    protected function down()
    {
        // Restore config entries to their defaults.
        DBManager::get()->exec("INSERT IGNORE INTO `config`
                ( `field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                VALUES (
                    'RANGE_TREE_ADMIN_PERM', 'root', 'string', 'global', 'permissions',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'mit welchem Status darf die Einrichtungshierarchie bearbeitet werden (admin oder root)'
                ), (
                    'SEM_TREE_ADMIN_PERM', 'root', 'string', 'global', 'permissions',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP() ,
                    'mit welchem Status darf die Veranstaltungshierarchie bearbeitet werden (admin oder root)'
                )");

        // Add database column for sem_tree institute assignments.
        DBManager::get()->exec("ALTER TABLE `sem_tree` ADD
            `studip_object_id` CHAR(32) CHARACTER SET latin1 COLLATE latin1_bin NULL DEFAULT NULL AFTER `name`");
        // Add index for studip_object_id.
        DBManager::get()->exec("ALTER TABLE `sem_tree` ADD INDEX `studip_object_id` (`studip_object_id`)");
    }

}
