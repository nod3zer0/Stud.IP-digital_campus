<?php

namespace Studip\Cli\Commands\Config;

use Course;
use Institute;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use User;
use RangeFactory;

class GetConfigValue extends Command
{
    protected static $defaultName = 'config:get';

    protected function configure(): void
    {
        $this->setDescription('Get value of a Stud.IP configuration key.');
        $this->setHelp('This command will return the value of a Stud.IP configuration key.');
        $this->addArgument('config-key', InputArgument::REQUIRED, 'Key of the configuration.');
        $this->addOption(
            'user',
            'u',
            InputOption::VALUE_OPTIONAL,
            'Read configuration for a user'
        );
        $this->addOption(
            'course',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Read configuration for a course'
        );
        $this->addOption(
            'inst',
            'i',
            InputOption::VALUE_OPTIONAL,
            'Read configuration for a institute'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configKey   = $input->getArgument('config-key');
        $userId      = $input->getOption('user');
        $courseId    = $input->getOption('course');
        $instituteId = $input->getOption('inst');

        $range = null;
        if ($userId && $courseId && $instituteId) {
            $output->writeln('<error>Please select one specific range</error>');
            return Command::FAILURE;
        }
        if ($userId) {
            $range = User::find($userId);
            if (empty($range)) {
                $output->writeln('<error>Could not find user</error>');
                return Command::FAILURE;
            }

        } else if ($courseId) {
            $range = Course::find($courseId);
            if (empty($range)) {
                $output->writeln('<error>Could not find course</error>');
                return Command::FAILURE;
            }
        } else if ($instituteId) {
            $range = Institute::find($instituteId);
            if (empty($range)) {
                $output->writeln('<error>Could not find institute</error>');
                return Command::FAILURE;
            }
        }

        if ($range) {
            $config = $range->getConfiguration();
        } else {
            $config = \Config::get();
        }

        if (empty($config)) {
            $output->writeln('<error>Could not find config</error>');
            return Command::FAILURE;
        }

        $metadata = $config->getMetadata($configKey) ?: [
            'field'       => $configKey,
            'type'        => 'string',
            'description' => 'missing in table `config`',
        ];
        if (isset($metadata['is_default'])) {
            $metadata['is_default'] = $metadata['is_default'] ? 'true' : 'false';
        }
        $metadata['value'] = $config->$configKey;
        $pairs             = array_map(null, array_keys($metadata), array_values($metadata));

        $table = new Table($output);
        $table->setHeaders(['Field', 'Value'])->setRows($pairs);
        $table->setStyle('box');
        $table->render();

        return Command::SUCCESS;
    }
}
