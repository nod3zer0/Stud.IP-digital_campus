<?php

namespace Studip\Cli\Commands\DB;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Dump extends Command
{
    protected static $defaultName = 'db:dump';

    protected function configure(): void
    {
        $this->setDescription('Dump the given database schema');
        $this->addArgument('path', InputArgument::REQUIRED, 'path where the backup should be saved');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dump_dir = realpath($input->getArgument('path'));
        $today = date('Ymd');
        $prefix = \Config::get()->STUDIP_INSTALLATION_ID ? \Config::get()->STUDIP_INSTALLATION_ID : 'studip';

        if (!is_writeable($dump_dir)) {
            $io->error('Directory: ' . $dump_dir . ' is not writeable!');
            return Command::FAILURE;
        }
        $dump_db_dir = $dump_dir . '/db-' . $today;
        if (!\is_dir($dump_db_dir)) {
            \mkdir($dump_db_dir);
        }
        $command = $this->getBaseDumpCommand();
        $output = [];
        $result_code = 0;

        foreach (\DBManager::get()->query('SHOW TABLES') as $tables) {
            $table = $tables[0];

            $dump_table = $dump_db_dir . '/' . $table . '-' . $today . '.sql';

            $io->writeln('<info>Dumping database table ' . $table . '</info>');

            $cmd = $command . ' ' . $table . ' > ' . $dump_table;
            $this->runCommand($cmd, $output, $result_code);

            if ($result_code > 0) {
                $io->error($this->parseOutput($cmd, $output));
                return Command::FAILURE;
            }
        }
        $dump_db = $dump_dir . '/' . $prefix . '-DB-' . $today . '.tar.gz';

        $io->writeln('<info>Packing database to ' . $dump_db . '</info>');

        $cmd = "cd $dump_db_dir && tar -czf $dump_db *";
        $this->runCommand($cmd, $output, $result_code);

        if ($result_code > 0) {
            $io->error($this->parseOutput($cmd, $output));
            return Command::FAILURE;
        }

        $cmd = "rm -rf $dump_db_dir";
        $this->runCommand($cmd, $output, $result_code);

        if ($result_code > 0) {
            $io->error($this->parseOutput($cmd, $output));
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Get the base dump command arguments for MySQL as a string.
     *
     * @return string
     */
    private function getBaseDumpCommand(): string
    {
        $command =
            'mysqldump ' .
            $this->getConnectionString() .
            ' --skip-add-locks --skip-comments --skip-set-charset --tz-utc';

        if (!\DBManager::get()->isMariaDB()) {
            $command .= ' --column-statistics=0 --set-gtid-purged=OFF';
        }
        return $command . ' ' . $GLOBALS['DB_STUDIP_DATABASE'];
    }

    /**
     * Generate a basic connection string (--socket, --host, --port, --user, --password) for the database.
     *
     * @return string
     */
    private function getConnectionString(): string
    {
        return ' --user="' .
            $GLOBALS['DB_STUDIP_USER'] .
            '"  --password="' .
            $GLOBALS['DB_STUDIP_PASSWORD'] .
            '"  --host="' .
            $GLOBALS['DB_STUDIP_HOST'] .
            '"';
    }

    /**
     * Execute dump command
     * @param string $cmd
     * @param array $output
     * @param int $result_code
     */
    private function runCommand(string $cmd, array &$output, int &$result_code)
    {
        exec($cmd . ' 2>&1', $output, $result_code);
    }

    /**
     * Parse output of exec()
     * @param string $cmd
     * @param array $output
     * @return string
     */
    private function parseOutput(string &$cmd, array &$output): string
    {
        $result = join('\n', array_merge([$cmd], $output));
        $cmd = '';
        $output = [];
        return $result;
    }
}
