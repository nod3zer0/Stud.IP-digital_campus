<?php

namespace Studip\Cli\Commands\Fix;

use DBManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Biest8136 extends Command
{
    protected static $defaultName = 'fix:biest-8136';

    protected function configure(): void
    {
        $this->setDescription('Fix Biest #8136');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $query = "UPDATE `activities`
          SET `actor_type` = 'anonymous',
              `actor_id` = ''
          WHERE `provider` = :provider
            AND `actor_type` != 'anonymous'
            AND `object_id` IN (
                SELECT `topic_id`
                FROM `forum_entries`
                WHERE `anonymous` != 0
            )";
        $statement = DBManager::get()->prepare($query);
        $statement->bindValue(':provider', 'Studip\\Activity\\ForumProvider');
        $statement->execute();

        $io->info(sprintf('%u forum post activities were anonymized', $statement->rowCount()));
        return Command::SUCCESS;
    }
}
