<?php

namespace Studip\Cli\Commands\Fix;

use DBManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Biest7866 extends Command
{
    protected static $defaultName = 'fix:biest-7866';

    protected function configure(): void
    {
        $this->setDescription('Fix Biest #7866');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $root_folders = DBManager::get()->fetchAll("SELECT `id`, `range_id` FROM `folders` WHERE `parent_id` = ''");

        foreach ($root_folders as $r) {
            $this->setFolderRangeId($io, $r['id'], $r['range_id']);
        }
        return Command::SUCCESS;
    }

    /**
     * Sets the range_id of all child folders to the given range_id.
     * @param SymfonyStyle $io
     * @param string $parent_folder
     * @param string $range_id
     */
    private function setFolderRangeId(SymfonyStyle $io, string $parent_folder, string $range_id)
    {
        // Update all child folder range_ids.
        DBManager::get()->execute('UPDATE `folders` SET `range_id` = :range WHERE `parent_id` = :parent', [
            'range' => $range_id,
            'parent' => $parent_folder,
        ]);

        // Recursion: set correct range_id for child folders with wrong range_id.
        $children = DBManager::get()->fetchAll('SELECT `id`, `range_id` FROM `folders` WHERE `parent_id` = :parent', [
            'parent' => $parent_folder,
        ]);
        foreach ($children as $child) {
            if ($child['range_id'] != $range_id) {
                $io->info(sprintf("Folder %s -> range_id %s.\n", $child['id'], $range_id));
            }
            $this->setFolderRangeId($io, $child['id'], $range_id);
        }
    }
}
