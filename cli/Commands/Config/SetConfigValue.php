<?php

namespace Studip\Cli\Commands\Config;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class SetConfigValue extends Command
{
    protected static $defaultName = 'config:set';

    protected function configure(): void
    {
        $this->setDescription('Set value of a Stud.IP configuration key.');
        $this->setHelp('This command will set the value of a Stud.IP configuration key.');
        $this->addArgument('config-key', InputArgument::REQUIRED, 'Key of the configuration.');
        $this->addArgument('config-value', InputArgument::REQUIRED, 'Value of the configuration.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configKey = $input->getArgument('config-key');
        $configValue = $input->getArgument('config-value');
        $metadata = \Config::get()->getMetadata($configKey);
        if (!$metadata) {
            throw new \RuntimeException("Unknown config key '{$configKey}");
        }

        \Config::get()->store($configKey, $configValue);

        return Command::SUCCESS;
    }
}
