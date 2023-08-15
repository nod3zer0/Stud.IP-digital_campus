<?php

namespace Studip\Cli\Commands\Plugins;

use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PluginInfo extends AbstractPluginCommand
{
    protected static $defaultName = 'plugin:info';

    protected function configure(): void
    {
        $this->setDescription('Shows information about matching plugins.');
        $this->setHelp('This command shows information about plugins whose name contains the optional pattern.');
        $this->addArgument('pattern', InputArgument::OPTIONAL, 'pattern to search for');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pattern = $input->getArgument('pattern');

        $pluginManager = \PluginManager::getInstance();
        $plugins = $pluginManager->getPluginInfos();

        if ($pattern) {
            $plugins = array_filter($plugins, function ($plugin) use ($pattern) {
                return false !== mb_stripos($plugin['name'], $pattern);
            });
        }

        $basepath = \Config::get()->PLUGINS_PATH;
        foreach ($plugins as $plugin) {
            $plugindir = $basepath . '/' . $plugin['path'];

            $plugin['class_exists'] = $this->pluginClassExists($plugindir, $plugin);
            $plugin['type'] = join(',', $plugin['type']);

            if (is_dir($plugindir . '/migrations')) {
                $schemaVersion = new \DBSchemaVersion($plugin['name']);
                $migrator = new \Migrator($plugindir . '/migrations', $schemaVersion);
                $plugin['pending_migrations'] = count($migrator->relevantMigrations(null));
                $plugin['schema_version'] = $schemaVersion->get();
            }

            $pairs = [];
            foreach ($plugin as $key => $value) {
                $pairs[] = [$key, $value];
            }

            $table = new Table($output);
            $table->setHeaders(['Field', 'Value'])->setRows($pairs);
            $table->setStyle('box');
            $table->render();

            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    private function pluginClassExists(string $plugindir, array $plugin)
    {
        $pluginfile = $plugindir . $plugin['class'] . '.class.php';
        if (file_exists($pluginfile)) {
            return 1;
        } else {
            $pluginfile = $plugindir . $plugin['class'] . '.php';
            if (file_exists($pluginfile)) {
                return 1;
            }
        }

        return 0;
    }
}
