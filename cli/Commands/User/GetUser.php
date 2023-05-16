<?php

namespace Studip\Cli\Commands\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use User;

class GetUser extends Command
{
    protected static $defaultName = 'user:get';

    protected function configure(): void
    {
        $this->setDescription('Get data of Stud.IP users.');
        $this->setHelp('This command will return the user data.');
        $this->addOption(
            'field',
            'f',
            InputOption::VALUE_OPTIONAL,
            'In which database field should be searched',
            'username'
        );
        $this->addArgument('needle', InputArgument::REQUIRED, 'Value to search for a user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $needle = $input->getArgument('needle');
        $field = $input->getOption('field');

        if ($field === 'id') {
            $field = 'user_id';
        }

        $users = User::findBySQL($field . ' = ?', [$needle]);

        if (empty($users)) {
            $output->writeln('<error>Could not find users</error>');
            return Command::FAILURE;
        }
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                $user->id,
                $user->username,
                $user->vorname,
                $user->nachname,
                $user->email,
                $user->auth_plugin,
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['Id', 'Username', 'Firstname', 'Lastname', 'Email', 'Auth-Plugin'])
            ->setRows($data)
            ->setStyle('box')
            ->render();

        return Command::SUCCESS;
    }
}
