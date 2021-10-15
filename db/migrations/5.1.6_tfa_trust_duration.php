<?php
final class TfaTrustDuration extends Migration
{
    public function description()
    {
        return 'TIC #11508: Configurable trust duration for devices';
    }

    protected function up()
    {
        // Create course config
        $query = "INSERT IGNORE INTO `config` (
                    `field`, `value`, `type`, `range`, `section`,
                    `mkdate`, `chdate`,
                    `description`
                  ) VALUES(
                    'TFA_TRUST_DURATION', '30', 'integer', 'global', 'Zwei-Faktor-Authentifizierung',
                    UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
                    'Dauer, denen Geräte vertraut werden soll in Tagen (0 für dauerhaftes Vertrauen)'
                  )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` = 'TFA_TRUST_DURATION'";
        DBManager::get()->exec($query);
    }
}
