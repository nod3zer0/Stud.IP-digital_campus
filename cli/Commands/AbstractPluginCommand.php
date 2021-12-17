<?php
namespace Studip\Cli\Commands;

abstract class AbstractPluginCommand extends AbstractCommand
{
    protected function findPluginByName(\PluginManager $pluginManager, string $pluginname): ?array
    {
        $plugins = $pluginManager->getPluginInfos();
        $found = array_filter($plugins, function ($plugin) use ($pluginname) {
            return mb_strtolower($pluginname) === mb_strtolower($plugin['name']);
        });

        return count($found) ? reset($found) : null;
    }

    protected function findPluginNameByFolder(string $folder)
    {
        var_dump('foo');die;
        return 'foo';
    }
}
