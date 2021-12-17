<?php

namespace Studip\Cli\Commands\Cronjobs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronjobExecute extends Command
{
    protected static $defaultName = 'cronjobs:execute';

    protected function configure(): void
    {
        $this->setDescription('Execute cronjob task.');
        $this->setHelp('This command will execute a cronjob task.');
        $this->addArgument('task_id', InputArgument::REQUIRED, 'Id of the desired cron job');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $task_id = $input->getArgument('task_id');
        $task = \CronjobTask::find($task_id);
        if (!$task) {
            $output->writeln('<error>Unknown task id</error>');
            return Command::FAILURE;
        }
        if (!file_exists($GLOBALS['STUDIP_BASE_PATH'] . '/' . $task->filename)) {
            $output->writeln(sprintf('<error>Invalid task, unknown filename %s</error>', $task->filename));
            return Command::FAILURE;
        }
        require_once $GLOBALS['STUDIP_BASE_PATH'] . '/' . $task->filename;
        if (!class_exists('\\' . $task->class)) {
            fwrite(STDOUT, 'Invalid task, unknown class "' . $task->class . '"' . PHP_EOL);
            $output->writeln(sprintf('<error>Invalid task, unknown class %s</error>', $task->class));
            return Command::FAILURE;
        }
        $task->engage('');
        return Command::SUCCESS;
    }
}
