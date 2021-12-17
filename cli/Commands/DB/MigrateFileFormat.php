<?php

namespace Studip\Cli\Commands\DB;

use DBManager;
use StudipPDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateFileFormat extends Command
{
    protected static $defaultName = 'db:migrate-file-format';

    protected function configure(): void
    {
        $this->setDescription('Antelope to Barracuda');
        $this->setHelp('Migrate the FileFormat from Antelope to Barracuda.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Migration starting at ' . date('d.m.Y H:i:s'));
        $start = microtime(true);
        // Check if InnoDB is enabled in database server.
        $engines = DBManager::get()->fetchAll('SHOW ENGINES');
        $innodb = false;
        foreach ($engines as $e) {
            // InnoDB is found and enabled.
            if ($e['Engine'] == 'InnoDB' && in_array(mb_strtolower($e['Support']), ['default', 'yes'])) {
                $innodb = true;
                break;
            }
        }
        if ($innodb) {
            // Get version of database system (MySQL/MariaDB/Percona)
            $data = DBManager::get()->fetchFirst('SELECT VERSION() AS version');
            $version = $data[0];

            // Use Barracuda format if database supports it (5.5 upwards).
            if (version_compare($version, '5.5', '>=')) {
                $io->info('Checking if Barracuda file format is supported...');
                // Get innodb_file_per_table setting
                $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_per_table'");
                $file_per_table = $data['Value'];

                // Check if Barracuda file format is enabled
                $data = DBManager::get()->fetchOne("SHOW VARIABLES LIKE 'innodb_file_format'");
                $file_format = $data['Value'];
                if (mb_strtolower($file_per_table) == 'on' && mb_strtolower($file_format) == 'barracuda') {
                    // Fetch all tables that need to be converted.
                    $tables = DBManager::get()->fetchFirst(
                        "SELECT TABLE_NAME
                FROM `information_schema`.TABLES
                WHERE TABLE_SCHEMA=:database AND ENGINE=:engine
                    AND ROW_FORMAT IN (:rowformats)
                ORDER BY TABLE_NAME",
                        [
                            ':database' => $GLOBALS['DB_STUDIP_DATABASE'],
                            ':engine' => 'InnoDB',
                            ':rowformats' => ['Compact', 'Redundant'],
                        ]
                    );

                    $newformat = 'DYNAMIC';

                    // Prepare query for table conversion.
                    $stmt = DBManager::get()->prepare('ALTER TABLE :database.:table ROW_FORMAT=:newformat');
                    $stmt->bindParam(':database', $DB_STUDIP_DATABASE, StudipPDO::PARAM_COLUMN);
                    $stmt->bindParam(':newformat', $newformat, StudipPDO::PARAM_COLUMN);

                    if (count($tables) > 0) {
                        // Now convert the found tables.
                        foreach ($tables as $t) {
                            $local_start = microtime(true);
                            $stmt->bindParam(':table', $t, StudipPDO::PARAM_COLUMN);
                            $stmt->execute();
                            $local_end = microtime(true);
                            $local_duration = $local_end - $local_start;
                            $human_local_duration = sprintf(
                                '%02d:%02d:%02d',
                                ($local_duration / 60 / 60) % 24,
                                ($local_duration / 60) % 60,
                                $local_duration % 60
                            );
                            $io->info('Converserion of table' . $t . ' took ' . $human_local_duration);
                        }
                    } else {
                        $io->error('No Antelope format tables found');
                        return Command::FAILURE;
                    }
                } else {
                    if (mb_strtolower($file_per_table) != 'on') {
                        $io->error('file_per_table not set');
                        return Command::FAILURE;
                    }
                    if (mb_strtolower($file_format) != 'barracuda') {
                        $io->error('file_format not set to Barracuda (but to ' . $file_format . ')');
                        return Command::FAILURE;
                    }
                }
                $end = microtime(true);
                $duration = $end - $start;
                $human_duration = sprintf(
                    '%02d:%02d:%02d',
                    ($duration / 60 / 60) % 24,
                    ($duration / 60) % 60,
                    $duration % 60
                );

                $io->info('Migration finished at ' . date('d.m.Y H:i:s') . ', duration ' . $human_duration);
                return Command::SUCCESS;
            } else {
                $io->error(
                    'Your database server does not yet support the Barracuda row format (you need at least 5.5)'
                );
                return Command::FAILURE;
            }
        } else {
            $io->error(
                'The storage engine InnoDB is not enabled in your database installation, tables cannot be converted.'
            );
            return Command::INVALID;
        }
    }
}
