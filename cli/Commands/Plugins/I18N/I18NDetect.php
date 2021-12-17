<?php
namespace Studip\Cli\Commands\Plugins\I18N;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class I18NDetect extends I18NCommand
{
    protected static $defaultName = 'plugin:i18n:detect';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Detect unmarked strings.');
        $this->setHelp('This command detects probably unmarked strings for localization in php files.');
        $this->addOption('only-filenames', '1', InputOption::VALUE_NONE, 'display only the filenames');
        $this->addOption('absolute-filenames', 'a', InputOption::VALUE_NONE, 'display absolute filenames');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $folder = $this->getPluginFolder($input);
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }

        $iterator = $this->getFolderIterator($folder, true, ['php']);

        $found = false;
        foreach ($iterator as $file) {
            $filename = $file->getPathName();

            $matched = preg_match('/(?<![$>])_\(/', file_get_contents($filename));
            if ($matched) {
                $output_filename = $input->getOption('absolute-filenames')
                                 ? $filename
                                 : $this->relativeFilePath($filename, true);

                $message = $input->getOption('only-filenames')
                         ? $output_filename
                         : "<info>{$output_filename}</info>: {$matched} occurence(s)";

                $output->writeln($message);
                $found = true;
            }
        }

        if (!$found) {
            $output->writeln('<info>No unmarked translation strings found</info>');
        }

        return Command::SUCCESS;
    }
}
