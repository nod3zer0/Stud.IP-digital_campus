<?php

namespace Studip\Cli\Commands\Cronjobs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronjobWorker extends Command
{
    protected static $defaultName = 'cronjobs:worker';

    protected function configure(): void
    {
        $this->setDescription('Cronjob worker.');
        $this->setHelp('Worker process for the cronjobs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        \CronjobScheduler::getInstance()->run();
        return Command::SUCCESS;
    }
}
