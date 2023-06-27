<?php

namespace Studip\Cli\Commands\User;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UsersDelete extends Command
{
    protected static $defaultName = 'user:delete';

    protected function configure(): void
    {
        $this->setDescription('Delete users.');
        $this->setHelp('Delete multiple studip user accounts');
        $this->addArgument('range', InputArgument::REQUIRED, 'Path to csv-file or - to read from STDIN');
        $this->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'Send a deletion email', true);
        $this->addOption(
            'delete_admins',
            'd',
            InputOption::VALUE_OPTIONAL,
            'Admins can also be deleted on request',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $range = $input->getArgument('range');
        $email = $input->getOption('email');
        $delete_admins = $input->getOption('delete_admins');

        if ($range === '-') {
            $file = STDIN;
        } elseif (is_file($range)) {
            $file = fopen($range, 'r');
        } else {
            $output->writeln(sprintf('<error>File not found: %s</error>', $range));
            return Command::FAILURE;
        }

        $list = '';
        while (!feof($file)) {
            $list .= fgets($file, 1024);
        }
        $usernames = preg_split('/[\s,;]+/', $list, -1, PREG_SPLIT_NO_EMPTY);
        $usernames = array_unique($usernames);

        \User::findEachBySQL(
            function (\User $user) use ($output, $delete_admins, $email) {
                if (!$delete_admins && ($user->perms == 'admin' || $user->perms == 'root')) {
                    $output->writeln(sprintf('User: %s is %s, NOT deleted', $user->username, $user->perms));
                    return;
                }

                $umanager = new \UserManagement($user->id);
                //wenn keine Email gewünscht, Adresse aus den Daten löschen
                if (!$email) {
                    $umanager->user_data['auth_user_md5.Email'] = '';
                }
                if ($umanager->deleteUser()) {
                    $output->writeln(sprintf('<info>User: %s successfully deleted:</info>', $user->username));
                } else {
                    $output->writeln(sprintf('<error>User: %s NOT deleted</error>', $user->username));
                }
                $output->writeln(parse_msg_to_clean_text($umanager->msg));
            },
            'username IN (?)',
            [$usernames]
        );

        return Command::SUCCESS;
    }
}
