<?php

namespace Studip\Cli\Commands\Migrate;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateMatrikelnummer extends Command
{
    protected static $defaultName = 'migrate:matrikelnummer';

    protected function configure() : void
    {
        $this->setDescription('Migrates the "Matrikelnummer" datafield into the matriculation_number column of the auth_user_md5 table.');
        $this->setHelp('This command migrates the "Matrikelnummer" datafield into the matriculation_number column of the auth_user_md5 table. The ID of the datafield must be provided. The datafield is not deleted in the migration process.');
        $this->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'The ID of the "Matrikelnummer" datafield.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $datafield_id = $input->getOption('id');
        if (!$datafield_id) {
            echo 'No ID for the "Matrikelnummer" datafield has been provided.' . "\n";
            return Command::FAILURE;
        }
        $datafield = \DataField::find($datafield_id);
        if (!$datafield) {
            echo 'The datafield could not be found.' . "\n";
            return Command::FAILURE;
        }
        //Get all entries of the datafield for users and set the
        //matriculation_number field:

        $verbose = $input->getOption('verbose');

        $modifier = function ($entry) use ($verbose) {
            if (!$entry->content) {
                return;
            }
            $user = \User::find($entry->range_id);
            if (!$user) {
                return;
            }
            $user->matriculation_number = $entry->content;
            if ($user->isDirty()) {
                $success = $user->store();
                if ($verbose) {
                    if ($success === false) {
                        printf('Error updating matriculation number for user %s.', $user->username) . "\n";
                    } elseif ($success > 0) {
                        printf('Matriculation number updated for user %s.', $user->username) . "\n";
                    }
                }
            }
        };

        \DatafieldEntryModel::findEachByDatafield_id(
            $modifier,
            $datafield->id
        );

        return Command::SUCCESS;
    }
}
