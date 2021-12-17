<?php

namespace Studip\Cli\Commands\Files;

use Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Dump extends Command
{
    protected static $defaultName = 'files:dump';

    protected function configure(): void
    {
        $this->setDescription('Dumping data directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'path where the backup should be saved');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dump_dir = realpath($input->getArgument('path'));
        $today = date('Ymd');
        $prefix = Config::get()->STUDIP_INSTALLATION_ID ? Config::get()->STUDIP_INSTALLATION_ID : 'studip';

        $data_path = realpath($GLOBALS['UPLOAD_PATH'] . '/../');

        if (!$data_path) {
            $io->error('Stud.IP upload folder not found!');
            return Command::FAILURE;
        }

        $dumb_data = $dump_dir . '/' . $prefix . '-DATA-' . $today . '.tar.gz';

        $io->info('Dumping data directory to ' . $dumb_data);

        $cmd = "cd $data_path && tar -czf $dumb_data ." . ' 2>&1';

        exec($cmd, $output, $ok);

        if ($ok > 0) {
            $io->error(join("\n", array_merge([$cmd], $output)));
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
