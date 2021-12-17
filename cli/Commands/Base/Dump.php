<?php

namespace Studip\Cli\Commands\Base;

use Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Dump extends Command
{
    protected static $defaultName = 'base:dump';

    protected function configure(): void
    {
        $this->setDescription('Dumping Stud.IP directory');
        $this->addArgument('path', InputArgument::REQUIRED, 'path where the backup should be saved');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dump_dir = realpath($input->getArgument('path'));
        $prefix = Config::get()->STUDIP_INSTALLATION_ID ? Config::get()->STUDIP_INSTALLATION_ID : 'studip';
        $today = date('Ymd');

        $base_path = realpath($GLOBALS['STUDIP_BASE_PATH']);

        if (!$base_path) {
            $io->error('Stud.IP directory not found!');
            return Command::FAILURE;
        }

        $dumb_studip = $dump_dir . '/' . $prefix . '-BASE-' . $today . '.tar.gz';

        $io->info('Dumping Stud.IP directory to' . $base_path);

        $cmd = "cd $base_path && tar -czf $dumb_studip ." . ' 2>&1';

        exec($cmd, $output, $ok);

        if ($ok > 0) {
            $io->error(join("\n", array_merge([$cmd], $output)));
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }
}
