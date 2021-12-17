<?php

namespace Studip\Cli\Commands\Users;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UserDelete extends Command
{
    protected static $defaultName = 'user:delete';

    protected function configure(): void
    {
        $this->setDescription('Delete users.');
        $this->setHelp('Delete multiple studip user accounts');
        $this->addArgument('range', InputArgument::REQUIRED, 'Username or path to csv-file');
        $this->addOption(
            'file_range',
            'f',
            InputOption::VALUE_OPTIONAL,
            'Set to true, if you want use a txt file with username',
            true
        );
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
        $file_range = $input->getOption('file_range');
        $email = $input->getOption('email');
        $delete_admins = $input->getOption('delete_admins');

        if ($email && !($MAIL_LOCALHOST && $MAIL_HOST_NAME && $ABSOLUTE_URI_STUDIP)) {
            $output->writeln(
                "<error>To use this script you MUST set correct values for $MAIL_LOCALHOST, $MAIL_HOST_NAME and $ABSOLUTE_URI_STUDIP in local.inc!</error>"
            );
        }

        if (!(bool) $file_range) {
            $usernames = [$range];
        } else {
            if (!is_file($range)) {
                $output->writeln(sprintf('<error>File not found: %s</error>', $range));
                return Command::FAILURE;
            }
            $file = fopen($range, 'r');
            $list = '';
            while (!feof($file)) {
                $list .= fgets($file, 1024);
            }
            $usernames = preg_split('/[\s,;]+/', $list, -1, PREG_SPLIT_NO_EMPTY);
            $usernames = array_unique($usernames);
        }

        $users = \User::findBySQL('username IN (?)', [$usernames]);

        if (!empty($users)) {
            foreach ($users as $user) {
                if (!$delete_admins && ($user->perms == 'admin' || $user->perms == 'root')) {
                    $output->writeln(sprintf('User: %s is %s, NOT deleted', $user->username, $user->perms));
                } else {
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
                }
            }
        }
        return Command::SUCCESS;
    }
}
