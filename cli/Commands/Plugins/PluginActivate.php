<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginActivate extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:activate';

    protected function configure(): void
    {
        $this->setDescription('Activate a plugin.');
        $this->setHelp('This command activates a plugin.');
        $this->addArgument('pluginname', InputArgument::REQUIRED, 'name of the plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginname = $input->getArgument('pluginname');

        $pluginManager = \PluginManager::getInstance();
        $plugin = $this->findPluginByName($pluginManager, $pluginname);
        if (null === $plugin) {
            $output->writeln('<error>Could not find plugin of that name.</error>');

            return Command::FAILURE;
        }

        $pluginManager->setPluginEnabled($plugin['id'], true);
        $output->writeln('Plugin activated.');

        return Command::SUCCESS;
    }
}
