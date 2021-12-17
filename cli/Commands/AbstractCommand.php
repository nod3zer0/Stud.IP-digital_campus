<?php
namespace Studip\Cli\Commands;

use FilesystemIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * Returns a folder iterator accessing all files inside that folder.
     *
     * @param string     $folder     Folder to return iterator for
     * @param bool       $recursive  Recurse into subfolders as well
     * @param array|null $extensions Optional list of extensions for files to be returned
     *
     * @return Iterator
     */
    protected function getFolderIterator(string $folder, bool $recursive = false, ?array $extensions = null): Iterator
    {
        if ($recursive) {
            $iterator = new RecursiveDirectoryIterator(
                $folder,
                FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS
            );
            $iterator = new RecursiveIteratorIterator($iterator);
        } else {
            $iterator = new FilesystemIterator($folder);
        }

        if ($extensions) {
            $extensions = array_map(function ($extension) {
                return preg_quote($extension, '/');
            }, $extensions);
            $iterator = new RegexIterator(
                $iterator,
                '/\.(?:' . implode('|', $extensions) . ')$/',
                RecursiveRegexIterator::MATCH
            );
        }

        return $iterator;
    }

    protected function relativeFilePath(string $filepath, bool $plugin = false): string
    {
        $filepath = str_replace($GLOBALS['STUDIP_BASE_PATH'] . '/', '', $filepath);

        if ($plugin) {
            $filepath = str_replace('public/plugins_packages/', '', $filepath);
        }

        return $filepath;
    }

    protected function absoluteFilePath(string $filepath, bool $plugin = false): string
    {
        if ($plugin && mb_strpos($filepath, 'public/plugins_packages') === false) {
            $filepath = 'public/plugins_packages/' . ltrim($filepath, '/');
        }

        if (mb_strpos($filepath, $GLOBALS['STUDIP_BASE_PATH']) === false) {
            $filepath = $GLOBALS['STUDIP_BASE_PATH'] . '/' . ltrim($filepath, '/');
        }

        return $filepath;
    }
}
