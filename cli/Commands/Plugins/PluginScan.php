<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginScan extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:scan';

    protected function configure(): void
    {
        $this->setDescription('Scans for unregistered plugins.');
        $this->setHelp(
            'This command scans the plugin path for plugin.manifest files belonging to not registered plugins.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pluginAdmin = new \PluginAdministration();
        $pluginManager = \PluginManager::getInstance();
        foreach ($pluginAdmin->scanPluginDirectory() as $manifest) {
            if (!$pluginManager->getPluginInfo($manifest['pluginclassname'])) {
                $pairs = [];
                foreach ($manifest as $key => $value) {
                    $pairs[] = [$key, is_array($value) ? join(",", $value) : (string) $value];
                }

                $table = new Table($output);
                $table->setHeaders(['Field', 'Value'])->setRows($pairs);
                $table->setStyle('box');
                $table->render();

                $output->writeln('');
            }
        }

        return Command::SUCCESS;
    }
}
