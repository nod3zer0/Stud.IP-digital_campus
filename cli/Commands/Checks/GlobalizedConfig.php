<?php
namespace Studip\Cli\Commands\Checks;

use Config;
use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Studip\Cli\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class GlobalizedConfig extends AbstractCommand
{
    protected static $defaultName = 'check:globalized-config';

    protected function configure(): void
    {
        $this->setDescription(
            '<href=https://develop.studip.de/trac/ticket/5671>TIC 5671</> scanner - Globalized config'
        );
        $this->setHelp(
            'Scans files for occurences of globalized config items (see <href=https://develop.studip.de/trac/ticket/5671>ticket 5671</> for more info)'
        );

        $this->addOption('filenames', 'f', InputOption::VALUE_NONE, 'Display filenames only (excludes -m and -o)');
        $this->addOption('matches', 'm', InputOption::VALUE_NONE, 'Show matched config variables');
        $this->addOption(
            'recursive',
            'r',
            InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
            'Do not scan recursively into subfolders'
        );
        $this->addOption('occurences', 'o', InputOption::VALUE_NONE, 'Show occurences in files');

        $this->addArgument(
            'folder',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Folder(s) to scan (pass the special value of "plugins" to scan the plugin folder)',
            [$GLOBALS['STUDIP_BASE_PATH']]
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $only_filenames = (bool) $input->getOption('filenames');
        $show_occurences = !$only_filenames && ($output->isVerbose() || $input->getOption('occurences'));
        $show_matches = !$only_filenames && ($show_occurences || $input->getOption('matches'));
        $recursive = $input->getOption('recursive') ?? true;

        $folders = $input->getArgument('folder');
        foreach ($folders as $index => $folder) {
            if ($folder === 'plugins') {
                $folders[$index] = $GLOBALS['STUDIP_BASE_PATH'] . '/public/plugins_packages/';
            }
        }
        $folders = array_unique($folders);

        $config = Config::get()->getFields('global');
        $quoted = array_map(function ($item) {
            return preg_quote($item, '/');
        }, $config);
        $regexp = '/\$(?:GLOBALS\[["\']?)?(' . implode('|', $quoted) . ')\b/S';

        foreach ($folders as $folder) {
            if (!file_exists($folder) || !is_dir($folder)) {
                $output->writeln(
                    "Skipping non-folder argument <fg=red>{$folder}</>",
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }
            $output->writeln("Scanning {$folder}", OutputInterface::VERBOSITY_VERBOSE);

            foreach ($this->getFolderIterator($folder, $recursive, ['php', 'tpl', 'inc']) as $file) {
                $filename = $file->getPathName();
                $contents = file_get_contents($filename);

                $output->writeln(
                    sprintf(
                        'Check <fg=magenta>%s</>',
                        $this->relativeFilePath($filename)
                    ),
                    OutputInterface::VERBOSITY_VERBOSE
                );

                if ($matched = preg_match_all($regexp, $contents, $matches)) {
                    if ($only_filenames) {
                        $output->writeln($filename);
                    } else {
                        $output->writeln(
                            sprintf(
                                '%u matched variable(s) in <fg=green;options=bold>%s</>',
                                $matched,
                                $this->shorten($filename)
                            )
                        );
                        if ($show_matches) {
                            $variables = array_unique($matches[1]);
                            foreach ($variables as $variable) {
                                $output->writeln("> <fg=cyan>{$variable}</>");
                                if ($show_occurences) {
                                    $output->writeln($this->highlight($contents, $variable));
                                }
                            }
                        }
                    }
                }
            }
        }

        return Command::SUCCESS;
    }

    private function highlight(string $content, string $variable): string
    {
        $lines = explode("\n", $content);

        $result = [];
        foreach ($lines as $index => $line) {
            if (mb_strpos($line, $variable) === false) {
                continue;
            }
            $result[$index + 1] = $line;
        }

        if (!$result) {
            return '';
        }

        $max = max(array_map('mb_strlen', array_keys($result)));

        foreach ($result as $index => $line) {
            $result[$index] = sprintf(
                "<fg=yellow>:%0{$max}u:</> %s",
                $index,
                str_replace($variable, "<fg=black;bg=yellow>{$variable}</>", $line)
            );
        }

        return implode("\n", $result);
    }
}
