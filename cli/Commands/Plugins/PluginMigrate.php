<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginMigrate extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:migrate';

    protected function configure(): void
    {
        $this->setDescription('Migrate a plugin.');
        $this->setHelp('This command migrates a plugin.');
        $this->addArgument('pluginname', InputArgument::REQUIRED, 'name of the plugin');
        $this->addOption('branch', 'b', InputOption::VALUE_OPTIONAL, 'branch of the migrations', '0');
        $this->addOption(
            'target',
            't',
            InputOption::VALUE_REQUIRED,
            'the id number of the migration to migrate to',
            null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginname = $input->getArgument('pluginname');
        $branch = $input->getOption('branch');
        $target = $input->getOption('target');
        $verbose = $input->getOption('verbose');

        if (null !== $target) {
            $target = (int) $target;
        }

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

        $migrator->migrateTo($target);

        return Command::SUCCESS;
    }
}
