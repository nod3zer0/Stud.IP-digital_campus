<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginStatusMigrations extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:status-migrations';

    protected function configure(): void
    {
        $this->setDescription('Shows the state of all migrations of a plugin.');
        $this->setHelp('This command shows the state of all migrations of a plugin.');
        $this->addArgument('pluginname', InputArgument::REQUIRED, 'name of the plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginname = $input->getArgument('pluginname');
        $verbose = $input->getOption('verbose');

        $pluginManager = \PluginManager::getInstance();
        $plugin = $this->findPluginByName($pluginManager, $pluginname);
        if (null === $plugin) {
            $output->writeln('<error>Could not find plugin of that name.</error>');

            return Command::FAILURE;
        }

        $pluginpath = \Config::get()->PLUGINS_PATH . '/' . $plugin['path'];

        if (!is_dir($pluginpath . '/migrations')) {
            $output->writeln('<comment>Could not find any migrations of that plugin.</comment>');

            return Command::SUCCESS;
        }

        // if there are migrations, migrate
        $schemaVersion = new \DBSchemaVersion($plugin['name']);
        $migrator = new \Migrator($pluginpath . '/migrations', $schemaVersion, $verbose);


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
