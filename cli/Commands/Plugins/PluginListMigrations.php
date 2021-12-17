<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginListMigrations extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:list-migrations';

    protected function configure(): void
    {
        $this->setDescription('List all migrations of a plugin.');
        $this->setHelp('This command lists all migrations of a plugin.');
        $this->addArgument('pluginname', InputArgument::REQUIRED, 'name of the plugin');
        $this->addOption('branch', 'b', InputOption::VALUE_OPTIONAL, 'branch of the migrations', '0');
        $this->addOption('target', 't', InputOption::VALUE_OPTIONAL, 'target of the migrator', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginname = $input->getArgument('pluginname');
        $branch = $input->getOption('branch');
        $target = $input->getOption('target');
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
        $schemaVersion = new \DBSchemaVersion($plugin['name'], $branch);
        $migrator = new \Migrator($pluginpath . '/migrations', $schemaVersion, $verbose);

        $migrations = $migrator->relevantMigrations($target);

        $rows = [];
        foreach ($migrations as $number => $migration) {
            $description = $migration->description() ?: '(no description)';
            $rows[] = [$number, get_class($migration), $description];
        }

        $table = new Table($output);
        $table->setHeaders(['ID', 'Class', 'Description'])->setRows($rows);
        $table->setStyle('box');
        $table->render();

        return Command::SUCCESS;
    }
}
