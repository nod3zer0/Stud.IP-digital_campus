<?php
namespace Studip\Cli\Commands\Plugins\I18N;

use Exception;
use Studip\Cli\Commands\AbstractPluginCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

abstract class I18NCommand extends AbstractPluginCommand
{
    private $plugin_manager = null;

    protected function configure(): void
    {
        $this->addOption('pluginname', 'p', InputArgument::OPTIONAL, 'name of the plugin');
        $this->addOption('folder', 'f', InputArgument::OPTIONAL, 'folder to scan (overrides pluginname)');
    }

    protected function getPluginFolder(InputInterface $input): string
    {
        $pluginname = $input->getOption('pluginname');
        $folder     = $input->getOption('folder');

        if (!$pluginname && !$folder) {
            throw new Exception('You must specify either pluginname or folder.');
        }

        if (!$folder && $pluginname) {
            $plugin = $this->findPluginByName($this->getPluginManager(), $pluginname);
            if ($plugin === null) {
                throw new Exception('Could not find plugin of that name.');
            }

            $folder = "{$GLOBALS['PLUGINS_PATH']}/{$plugin['path']}";
        }

        if (!$folder || !file_exists($folder) || !is_readable($folder)) {
            throw new Exception('Could not access folder.');
        }

        return $folder;
    }

    protected function getPluginManifest(string $folder): array
    {
        return $this->getPluginManager()->getPluginManifest($folder);
    }

    protected function getPluginLocaleDomainByFolder(string $folder): string
    {
        $manifest = $this->getPluginManifest($folder);
        if (!$manifest) {
            throw new Exception("Could not detect plugin manifest in folder {$folder}");
        }

        if (!isset($manifest['localedomain'])) {
            throw new Exception('Manifest has no defined localedomain');
        }

        return $manifest['localedomain'];
    }

    protected function getPluginManager(): \PluginManager
    {
        if ($this->plugin_manager === null) {
            $this->plugin_manager = \PluginManager::getInstance();
        }
        return $this->plugin_manager;
    }

    protected function getSystemLanguages(): array
    {
        return array_map(function ($lang) {
            return explode('_', $lang)[0];
        }, array_keys($GLOBALS['INSTALLED_LANGUAGES']));
    }
}
