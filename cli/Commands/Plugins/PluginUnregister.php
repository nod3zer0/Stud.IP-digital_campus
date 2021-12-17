<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PluginUnregister extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:unregister';

    protected function configure(): void
    {
        $this->setDescription('Unregister a plugin.');
        $this->setHelp('This command unregisters a plugin.');
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

        $pluginManager->unregisterPlugin($plugin['id']);

        // if there are any migrations, un-migrate
        $pluginpath = \Config::get()->PLUGINS_PATH . '/' . $plugin['path'];
        if (is_dir($pluginpath . '/migrations')) {
            $schemaVersion = new \DBSchemaVersion($plugin['name']);
            $migrator = new \Migrator($pluginpath . '/migrations', $schemaVersion, $verbose);

            $migrator->migrateTo(0);
        }

        $output->writeln('The plugin was unregistered successfully.');

        return Command::SUCCESS;
    }
}
