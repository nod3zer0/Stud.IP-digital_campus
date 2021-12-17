<?php
namespace Studip\Cli\Commands\Checks;

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use Studip\Cli\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Compatibility extends AbstractCommand
{
    protected static $defaultName = 'check:compatibility';

    protected function configure(): void
    {
        $this->setDescription('Compatibility scanner');
        $this->setHelp('Scans plugins for common issues (backward compatibility and the like)');

        $this->addArgument(
            'version',
            InputArgument::OPTIONAL,
            'Version to check against (if not suppied, all checks are performed)'
        );

        $this->addArgument(
            'folder',
            InputArgument::IS_ARRAY,
            'Folder to scan (will default to the plugins_packages folder)'
        );

        $this->addOption('filenames', 'f', InputOption::VALUE_NONE, 'Display filenames only');
        $this->addOption(
            'recursive',
            'r',
            InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE,
            'Do not scan recursively into subfolders'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->getFormatter()->setStyle('issue', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('bold', new OutputFormatterStyle(null, null, ['bold']));

        $rules = $this->getCompatibilityRules($input->getArgument('version'));
        $folders = $input->getArgument('folder') ?: $this->getDefaultFolders();
        $recursive = $input->getOption('recursive') ?? true;

        foreach ($folders as $f) {
            $folder = $this->validateFolder($f);
            if (!$folder) {
                $output->writeln("<info>Skipping invalid folder {$f}</info>", OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            $issues = [];
            foreach ($this->getFolderIterator($folder, $recursive, ['php', 'tpl', 'inc', 'js']) as $file) {
                $filename = $file->getPathName();
                $output->writeln("<info>Checking {$filename}", OutputInterface::VERBOSITY_VERBOSE);
                if ($errors = $this->checkFilecontentsAgainstRules($filename, $rules)) {
                    $issues[$filename] = $errors;
                }
            }

            if (count($issues) === 0) {
                continue;
            }

            if (!$input->getOption('filenames')) {
                $issue_count = array_sum(array_map('count', $issues));
                $message = count($issues) === 1
                    ? '%u issue found in <bold>%s</bold>'
                    : '%u issues found in <bold>%s</bold>';

                $output->writeln(sprintf(
                    "<issue>{$message}</issue>",
                    $issue_count,
                    $this->relativeFilePath($folder)
                ));
            }

            foreach ($issues as $filename => $errors) {
                if ($input->getOption('filenames')) {
                    $output->writeln($filename);
                } else {
                    $output->writeln(sprintf(
                        '> File <fg=green;options=bold>%s</>',
                        $this->relativeFilePath($filename)
                    ));
                    foreach ($errors as $needle => $suggestion) {
                        $output->writeln(
                            sprintf('- <fg=cyan>%s</> -> %s', $needle, $suggestion ?: '<fg=red>No suggestion available')
                        );
                    }
                }
            }
        }

        return Command::SUCCESS;
    }

    private function getCompatibilityRules(?string $version): array
    {
        if ($version !== null) {
            if (!file_exists(__DIR__ . "/compatibility-rules/studip-{$version}.php")) {
                throw new \Exception("No rules defined for Stud.IP version {$version}");
            }

            return require __DIR__ . "/compatibility-rules/studip-{$version}.php";
        }

        $rules = [];
        foreach (glob(__DIR__ . '/compatbility-rules/*.php') as $file) {
            $version_rules = require $file;
            $rules = array_merge($rules, $version_rules);
        }

        return $rules;
    }

    private function getDefaultFolders(): array
    {
        $folders = rtrim($GLOBALS['STUDIP_BASE_PATH'], '/') . '/public/plugins_packages';
        $folders = glob($folders . '/*/*');
        return $folders;
    }

    private function validateFolder(string $folder)
    {
        if (!file_exists($folder) || !is_dir($folder)) {
            return false;
        }

        return $folder;
    }

    private function checkFilecontentsAgainstRules(string $filename, array $rules)
    {
        $errors = [];

        $contents = strtolower(file_get_contents($filename));
        foreach ($rules as $needle => $suggestion) {
            if ($this->checkRule($contents, $needle)) {
                $errors[$needle] = $suggestion;
            }
        }
        return $errors;
    }

    private function checkRule(string $contents, string $rule)
    {
        if ($rule[0] === '/' && $rule[strlen($rule) - 1] === '/') {
            return (bool) preg_match("{$rule}s", $contents);
        }

        return strpos($contents, strtolower($rule)) > 0;
    }
}
