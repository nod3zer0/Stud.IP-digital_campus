<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInstall extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:install';

    protected function configure(): void
    {
        $this->setDescription('Install a plugin.');
        $this->setHelp('This command installs a plugin from a ZIP file.');
        $this->addArgument('zipfile', InputArgument::REQUIRED, 'path to the ZIP file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $zipfile = $input->getArgument('zipfile');

        try {
            $plugin_admin = new \PluginAdministration();
            if (parse_url($zipfile, \PHP_URL_SCHEME)) {
                $plugin_admin->installPluginFromURL($zipfile);
            } else {
                $plugin_admin->installPlugin($zipfile);
            }
            $output->writeln('The plugin was installed successfully.');
        } catch (\PluginInstallationException $ex) {
            $output->writeln('<error>' . $ex->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
