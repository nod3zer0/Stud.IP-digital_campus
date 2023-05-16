<?php

namespace Studip\Cli\Commands\Config;

use ConfigurationModel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SectionList extends Command
{
    protected static $defaultName = 'config:section-list';

    protected function configure(): void
    {
        $this->setDescription('List all configuration sections');
        $this->setHelp('This command shows a list of available configuration sections.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ranges = ConfigurationModel::getConfig();
        $data = [];
        foreach (array_keys($ranges) as $range) {
            $data[] = [$range ?? _('Ohne Kategorie')];
        }
        $table  = new Table($output);
        $table->setHeaders(['Range'])
        ->setRows($data)
        ->setStyle('box')
        ->render();

        return Command::SUCCESS;
    }
}
