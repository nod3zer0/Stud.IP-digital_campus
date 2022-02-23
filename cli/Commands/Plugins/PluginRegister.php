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
        $pluginManager = \PluginManager::getInstance();
        $manifest = $pluginManager->getPluginManifest($pluginpath);
        if (!$manifest) {
            $output->writeln('<error>The plugin\'s manifest is missing.</error>');
            return Command::FAILURE;
        }

        // get plugin meta data
        $pluginclass = $manifest['pluginclassname'];
        $origin = $manifest['origin'];
        $minVersion = $manifest['studipMinVersion'];
        $maxVersion = $manifest['studipMaxVersion'];

        // check for compatible version
        if (
            (isset($minVersion) && \StudipVersion::olderThan($minVersion)) ||
            (isset($maxVersion) && \StudipVersion::newerThan($maxVersion))
        ) {
            $output->writeln('<error>The plugin is not compatible with this version of Stud.IP.</error>');
            return Command::FAILURE;
        }

        // determine the plugin path
        $pluginregistered = $pluginManager->getPluginInfo($pluginclass);

        // create database schema if needed
        if (isset($manifest['dbscheme']) && !$pluginregistered) {
            $schemafile = $pluginpath . '/' . $manifest['dbscheme'];
            $contents = file_get_contents($schemafile);
            $statements = preg_split("/;[[:space:]]*\n/", $contents, -1, PREG_SPLIT_NO_EMPTY);
            $db = \DBManager::get();
            foreach ($statements as $statement) {
                $db->exec($statement);
            }
        }

        // check for migrations
        if (is_dir($pluginpath . '/migrations')) {
            $schemaVersion = new \DBSchemaVersion($manifest['pluginname']);
            $migrator = new \Migrator($pluginpath . '/migrations', $schemaVersion);
            $migrator->migrateTo(null);
        }

        $pluginpath = $origin . '/' . $pluginclass;

        // now register the plugin in the database
        $pluginid = $pluginManager->registerPlugin($manifest['pluginname'], $pluginclass, $pluginpath);

        // register additional plugin classes in this package
        $additionalclasses = $manifest['additionalclasses'];

        if (is_array($additionalclasses)) {
            foreach ($additionalclasses as $class) {
                $pluginManager->registerPlugin($class, $class, $pluginpath, $pluginid);
            }
        }

        $output->writeln('The plugin was successfully registered.');

        return Command::SUCCESS;
    }
}
