<?php
namespace Studip\Cli\Commands\Plugins\I18N;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class I18NCompile extends I18NCommand
{
    protected static $defaultName = 'plugin:i18n:compile';

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Compile translations');
        $this->setHelp('This command compiles all .po files in the locale folder of the plugin.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $folder = $this->getPluginFolder($input);

            foreach (glob("{$folder}/locale/*/LC_MESSAGES/*.po") as $po) {
                $command_line = 'msgfmt "${:PO}" -o "${:MO}"';
                $process = Process::fromShellCommandline($command_line);

                try {
                    $process->mustRun(null, [
                        'PO' => $po,
                        'MO' => preg_replace('/\.po$/', '.mo', $po),
                    ]);

                    $out = $process->getOutput();
                    if ($out) {
                        $output->writeln($out, OutputInterface::VERBOSITY_VERBOSE);
                    }

                    $output->writeln("Translations have been compiled successfully.");
                } catch (ProcessFailedException $e) {
                    $output->writeln("<error>Could not execute shell command</error>");
                    $output->writeln($e->getmessage());
                    return Command::FAILURE;
                }
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return Command::FAILURE;
        }
    }
}
