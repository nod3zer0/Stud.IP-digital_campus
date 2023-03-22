<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginRegister extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:register';

    protected function configure(): void
    {
        $this->setDescription('Register a plugin.');
        $this->setHelp('This command registers an installed plugin.');
        $this->addArgument('pluginpath', InputArgument::REQUIRED, 'path to the plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginpath = $input->getArgument('pluginpath');

        try {
            // This will try to set the language to english so we have a
            // consistent usage of english in cli commands.
            setTempLanguage(false, 'en_GB');

            $plugin_administration = new \PluginAdministration();
            $plugin_administration->registerPlugin($pluginpath);
        } catch (\PluginInstallationException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        } finally {
            restoreLanguage();
        }

        $output->writeln('The plugin was successfully registered.');

        return Command::SUCCESS;
    }
}
