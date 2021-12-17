<?php

namespace Studip\Cli\Commands\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateStatus extends Command
{
    protected static $defaultName = 'migrate:status';

    protected function configure(): void
    {
        $this->setDescription('Shows the state of all migrations.');
        $this->setHelp('This command shows the state of all migrations.');

        $this->addOption('domain', 'd', InputOption::VALUE_OPTIONAL, 'the domain of the migrations', 'studip');

        $defaultPath = $GLOBALS['STUDIP_BASE_PATH'] . '/db/migrations';
        $this->addOption('path', 'p', InputOption::VALUE_OPTIONAL, 'the id of the migration to list to', $defaultPath);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domain = $input->getOption('domain');
        $path = $input->getOption('path');
        $verbose = $input->getOption('verbose');

        $version = new \DBSchemaVersion($domain);
        $migrator = new \Migrator($path, $version, $verbose);

        $migrations = $migrator->migrationClasses();
        uksort($migrations, 'version_compare');
        $migrations = array_reverse($migrations, true);

        $pending = $migrator->relevantMigrations(null);

        $rows = [];
        foreach ($migrations as $number => $migration) {
            $rows[] = [isset($pending[$number]) ? 'No' : 'Yes', $number, basename($migration[0], '.php')];
        }

        $table = new Table($output);
        $table->setHeaders(['Ran?', 'ID', 'Migration'])->setRows($rows);
        $table->setStyle('box');
        $table->render();

        return Command::SUCCESS;
    }
}
