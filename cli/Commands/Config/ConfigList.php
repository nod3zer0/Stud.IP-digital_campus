<?php

namespace Studip\Cli\Commands\Config;

use ConfigurationModel;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigList extends Command
{
    protected static $defaultName = 'config:list';

    protected function configure()
    {
        $this->setDescription('List all Stud.IP configuration.');
        $this->setHelp('This command shows a list of available configurations.');
        $this->addArgument('config-section', InputArgument::OPTIONAL, 'Section of the configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $section  = $input->getArgument('config-section');
        $sections = ConfigurationModel::getConfig($section);

        foreach ($sections as $section => $configs) {
            $data = [];
            foreach ($configs as $config) {
                $data[] = [
                    $config['field'],
                    $config['type'],
                    mila(kill_format((string)$config['value']),40),
                    $config['range'],
                     mila(kill_format((string)$config['description']),40),
                ];
            }
            $table = new Table($output);
            $table->setColumnMaxWidth(1, 10);
            $table->setColumnMaxWidth(2, 50);
            $table->setColumnMaxWidth(3, 10);
            $table->setColumnMaxWidth(4, 50);
            $table->setHeaderTitle($section ?? _('Ohne Kategorie'));
            $table->setHeaders(['Field', 'Type', 'Value', 'Range', 'Description']);
            $table->setRows($data);
            $table->setStyle('box');
            $table->render();
        }

        return Command::SUCCESS;
    }
}
