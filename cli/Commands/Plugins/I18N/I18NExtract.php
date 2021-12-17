<?php
namespace Studip\Cli\Commands\Plugins\I18N;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class I18NExtract extends I18NCommand
{
    protected static $defaultName = 'plugin:i18n:extract';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Extract localizable strings.');
        $this->setHelp('This command extracts the localizable string from php files into a .pot file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $folder   = $this->getPluginFolder($input);
            $manifest = $this->getPluginManifest($folder);
            $localedomain = $this->getPluginLocaleDomainByFolder($folder);

            foreach ($this->getSystemLanguages() as $lang) {
                $lang = explode('_', $lang)[0];
                $language_dir = "{$folder}/locale/{$lang}/LC_MESSAGES";
                if (!file_exists($language_dir)) {
                    mkdir($language_dir, 0755, true);
                }
            }

            $main_lang = $this->getSystemLanguages()[0];
            $pot_file  = "{$folder}/locale/{$main_lang}/LC_MESSAGES/{$localedomain}.pot";
            file_put_contents($pot_file, '');

            $command_line = implode(' | ', [
                'find "${:FOLDER}" -iname "*.php"',
                'xargs xgettext --keyword=_n:1,2 --from-code=UTF-8 -j -n --language=PHP --add-location=never --package-name="${:PACKAGENAME}" -o "${:POTFILE}"',
            ]);

            $process = Process::fromShellCommandline($command_line);

            try {
                $process->mustRun(null, [
                    'FOLDER'      => $folder,
                    'PACKAGENAME' => $manifest['pluginclassname'],
                    'POTFILE'     => $pot_file,
                ]);

                $out = $process->getOutput();
                if ($out) {
                    $output->writeln($out, OutputInterface::VERBOSITY_VERBOSE);
                }

                $output->writeln("Translation strings have been extracted successfully.");
                return Command::SUCCESS;
            } catch (ProcessFailedException $e) {
                $output->writeln("<error>Could not execute shell command</error>");
                $output->writeln($e->getmessage());
                return Command::FAILURE;
            }

        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
