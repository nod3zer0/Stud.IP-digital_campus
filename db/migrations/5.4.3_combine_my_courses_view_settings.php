<?php

/**
 * - MY_COURSES_TILED_DISPLAY
 * - MY_COURSES_TILED_DISPLAY_RESPONSIVE
 * - MY_COURSES_SHOW_NEW_ICONS_ONLY
 */
final class CombineMyCoursesViewSettings extends Migration
{
    const OLD_FIELDS = [
        'MY_COURSES_SHOW_NEW_ICONS_ONLY' => false,
        'MY_COURSES_TILED_DISPLAY' => false,
        'MY_COURSES_TILED_DISPLAY_RESPONSIVE' => true,
    ];

    public function description()
    {
        return 'Combines the different view settings for my courses into a single configuration';
    }

    protected function up()
    {
        // Add new configuration
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_VIEW_SETTINGS', ?, 'array', 'user',
                    'MeineVeranstaltungen', 'Konfiguration der Ansicht \"Meine Veranstaltungen\"',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->execute($query, [
            json_encode($this->convertOldConfig(['MY_COURSES_TILED_DISPLAY_RESPONSIVE' => true]))
        ]);

        // Migrate old settings
        $this->migrateOldConfigurations();

        // Drop old configuration
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (?)";
        DBManager::get()->execute($query, [
            array_keys(self::OLD_FIELDS),
        ]);
    }

    protected function down()
    {
        // Restore old configuration
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`,
                    `section`, `description`,
                    `mkdate`, `chdate`
                  ) VALUES (
                    'MY_COURSES_SHOW_NEW_ICONS_ONLY', 0, 'boolean', 'user',
                    'MeineVeranstaltungen', 'Nur Icons für neue Inhalte sollen angezeigt werden',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  ), (
                    'MY_COURSES_TILED_DISPLAY', 0, 'boolean', 'user',
                    'MeineVeranstaltungen', 'Hat die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  ), (
                    'MY_COURSES_TILED_DISPLAY_RESPONSIVE', 0, 'boolean', 'user',
                    'MeineVeranstaltungen', 'Hat die Kachelansicht unter \"Meine Veranstaltungen\" aktiviert (responsiv)',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
                  )";
        DBManager::get()->exec($query);

        // Migrate new settings
        $this->migrateNewConfigurations();

        // Drop new configuration
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'MY_COURSES_VIEW_SETTINGS'";
        DBManager::get()->exec($query);
    }

    private function migrateOldConfigurations(): void
    {
        $query = "SELECT `value`
                  FROM `config_values`
                  WHERE `range_id` = :user_id AND `field` = :field";
        $values_statement = DBManager::get()->prepare($query);

        $query = "INSERT IGNORE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`)
                  VALUES ('MY_COURSES_VIEW_SETTINGS', :user_id, :value, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
        $insert_statement = DBManager::get()->prepare($query);

        $query = "SELECT DISTINCT `range_id`
                  FROM `config_values`
                  WHERE `field` IN (?)";
        $user_ids = DBManager::get()->fetchFirst($query, [
            array_keys(self::OLD_FIELDS),
        ]);
        foreach ($user_ids as $user_id) {
            $values_statement->bindValue(':user_id', $user_id);

            $config = self::OLD_FIELDS;
            foreach (array_keys(self::OLD_FIELDS) as $field) {
                $values_statement->bindValue(':field', $field);
                $values_statement->execute();

                $config[$field] = $values_statement->fetchColumn();
            }

            $insert_statement->execute([
                ':user_id' => $user_id,
                ':value'   => json_encode($this->convertOldConfig($config)),
            ]);
        }
    }

    private function convertOldConfig(array $config): array
    {
        return [
            'regular' => [
                'tiled'    => (bool) ($config['MY_COURSES_TILED_DISPLAY'] ?? self::OLD_FIELDS['MY_COURSES_TILED_DISPLAY']),
                'only_new' => (bool) ($config['MY_COURSES_SHOW_NEW_ICONS_ONLY'] ?? self::OLD_FIELDS['MY_COURSES_SHOW_NEW_ICONS_ONLY']),
            ],
            'responsive' => [
                'tiled'    => (bool) ($config['MY_COURSES_TILED_DISPLAY_RESPONSIVE'] ?? self::OLD_FIELDS['MY_COURSES_TILED_DISPLAY_RESPONSIVE']),
                'only_new' => (bool) ($config['MY_COURSES_SHOW_NEW_ICONS_ONLY'] ?? self::OLD_FIELDS['MY_COURSES_SHOW_NEW_ICONS_ONLY']),
            ],
        ];
    }

    private function migrateNewConfigurations(): void
    {
        $query = "INSERT IGNORE INTO `config_values` (`field`, `range_id`, `value`, `mkdate`, `chdate`)
                  VALUES (:field, :user_id, :value, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
        $insert_statement = DBManager::get()->prepare($query);

        $query = "SELECT `range_id`, `value`
                  FROM `config_values`
                  WHERE `field` = 'MY_COURSES_VIEW_SETTINGS'";
        $statement = DBManager::get()->exec($query);
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        foreach ($statement as $row) {
            $config = json_decode($row['value'], true);

            $insert_statement->bindValue(':user_id', $row['user_id']);

            foreach ($this->convertNewConfig($config) as $field => $value) {
                if ($value !== self::OLD_FIELDS[$field]) {
                    $insert_statement->bindValue(':field', $field);
                    $insert_statement->bindValue(':value', (int) $value);
                    $insert_statement->execute();
                }
            }
        }
    }

    private function convertNewConfig(array $config): array
    {
        return [
            'MY_COURSES_SHOW_NEW_ICONS_ONLY' => $config['regular']['only_new'] || $config['responsive']['only_new'],
            'MY_COURSES_TILED_DISPLAY' => $config['regular']['tiled'],
            'MY_COURSES_TILED_DISPLAY_RESPONSIVE' => $config['responsive']['tiled'],
        ];
    }

}
