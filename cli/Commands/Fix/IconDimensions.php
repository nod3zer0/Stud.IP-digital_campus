<?php
namespace Studip\Cli\Commands\Fix;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class IconDimensions extends Command
{
    protected static $defaultName = 'fix:icon-dimensions';

    protected function configure(): void
    {
        $this->setDescription('Fix icon dimensions in their svg files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = $GLOBALS['STUDIP_BASE_PATH'] . '/public/assets/images/icons';
        $iterator = new RecursiveDirectoryIterator(
            $folder,
            FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::UNIX_PATHS
        );
        $iterator = new RecursiveIteratorIterator($iterator);
        $regexp_iterator = new RegexIterator($iterator, '/\.svg$/', RecursiveRegexIterator::MATCH);

        foreach ($regexp_iterator as $file) {
            $contents = file_get_contents($file);

            $xml = simplexml_load_string($contents);
            $attr = $xml->attributes();
            if ($attr->width && $attr->height) {
                continue;
            }

            $contents = str_replace('<svg ', '<svg width="16" height="16" ', $contents);
            file_put_contents($file, $contents);

            $output->writeln("Adjusted {$file}");
        }

        return Command::SUCCESS;
    }
}
