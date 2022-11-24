<?php

class ViruscheckForUploads extends Migration
{
    public function description()
    {
        return 'Provide config options for ClamAV usage on file uploads';
    }

    public function up()
    {
        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`,
                    `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)";

        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'        => 'VIRUSSCAN_ON_UPLOAD',
            'description' => 'Sollen Dateien beim Upload mit ClamAV auf Viren überprüft werden?',
            'type'        => 'boolean',
            'range'       => 'global',
            'section'     => 'files',
            'value'       => '0'
        ]);
        $statement->execute([
            'name'        => 'VIRUSSCAN_SOCKET',
            'description' => 'Pfad zum Unix Socket (wird statt TCP verwendet, falls etwas eingetragen ist)',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'files',
            'value'       => '/var/run/clamav/clamd.ctl'
        ]);
        $statement->execute([
            'name'        => 'VIRUSSCAN_HOST',
            'description' => 'Host des Virenscanners (wird nur verwendet, falls kein Socket eingetragen ist)',
            'type'        => 'string',
            'range'       => 'global',
            'section'     => 'files',
            'value'       => '127.0.0.1'
        ]);
        $statement->execute([
            'name'        => 'VIRUSSCAN_PORT',
            'description' => 'Port des Virenscanners (wird nur verwendet, falls kein Socket eingetragen ist)',
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'files',
            'value'       => 3310
        ]);
        $statement->execute([
            'name'        => 'VIRUSSCAN_MAX_STREAMLENGTH',
            'description' => 'Maximale Streamlänge in Bytes, die beim Virenscanner erlaubt ist',
            'type'        => 'integer',
            'range'       => 'global',
            'section'     => 'files',
            'value'       => 26214400
        ]);
    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'VIRUSSCAN_ON_UPLOAD',
                      'VIRUSSCAN_SOCKET',
                      'VIRUSSCAN_HOST',
                      'VIRUSSCAN_PORT',
                      'VIRUSSCAN_MAX_STREAMLENGTH'
                  )";
        DBManager::get()->exec($query);
    }
}
