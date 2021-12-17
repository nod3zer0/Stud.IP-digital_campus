<?php

namespace Studip\Cli\Commands\Cronjobs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronjobList extends Command
{
    protected static $defaultName = 'cronjobs:list';

    protected function configure(): void
    {
        $this->setDescription('List cronjobs.');
        $this->setHelp('This command lists all available cronjobs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tasks = \CronjobTask::findBySql('1');

        if ($tasks) {
            $table = new Table($output);
            $table->setStyle('compact');
            $table->setHeaders(['Task-ID', 'Description']);
            foreach ($tasks as $task) {
                $description = call_user_func(['\\' . $task->class, 'getDescription']);
                if ($description) {
                    $table->addRow([$task->id, $description]);
                }
            }
            $table->render();
        }
        return Command::SUCCESS;
    }
}
