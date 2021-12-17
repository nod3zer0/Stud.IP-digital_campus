<?php

namespace Studip\Cli\Commands\Fix;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * For example
 *
 * php cli/studip fix:biest-7789 extern_config config
 * php cli/studip fix:biest-7789 aux_lock_rules attributes
 * php cli/studip fix:biest-7789 aux_lock_rules sorting
 * php cli/studip fix:biest-7789 user_config value "field = 'MY_COURSES_ADMIN_VIEW_FILTER_ARGS'"
 * php cli/studip fix:biest-7789 mail_queue_entries mail
 */
class Biest7789 extends Command
{
    protected static $defaultName = 'fix:biest-7789';

    protected function configure(): void
    {
        $this->setDescription('Fix Biest #7789');
        $this->addArgument('table', InputArgument::REQUIRED, 'database table');
        $this->addArgument('column', InputArgument::REQUIRED, 'table column');
        $this->addArgument('where', InputArgument::OPTIONAL, 'where clause');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('default_charset', 'utf-8');
        $io = new SymfonyStyle($input, $output);
        $table = $input->getArgument('table');
        $column = $input->getArgument('column');
        $where = $input->getArgument('where');

        $db = \DBManager::get();

        $io->title($table);
        // get primary keys
        $result = $db->query("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'");
        $keys = [];

        while ($data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $keys[] = $data['Column_name'];
        }

        // retrieve and convert data
        $result = $db->query(
            'SELECT `' . implode('`,`', $keys) . "`, `$column` FROM `$table` WHERE " . ($where ?: '1')
        );

        while ($data = $result->fetch(\PDO::FETCH_ASSOC)) {
            $content = unserialize(\legacy_studip_utf8decode($data[$column]));

            if ($content === false) {
                // try to fix string length denotations
                $fixed = preg_replace_callback(
                    '/s:([0-9]+):\"(.*?)\";/s',
                    function ($matches) {
                        return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
                    },
                    $data[$column]
                );

                $content = unserialize(\legacy_studip_utf8decode($fixed));
            }

            if ($content !== false) {
                // encode all data
                $json = json_encode($this->legacy_studip_utf8encode($content), true);

                $query = "UPDATE `$table` SET `$column` = " . $db->quote($json) . "\n WHERE ";

                $where_query = [];
                foreach ($keys as $key) {
                    $where_query[] = "`$key` = " . $db->quote($data[$key]);
                }

                $q = $query . implode(' AND ', $where_query);
                $db->exec($q);
                $io->writeln("<info>$q</info>");
            } else {
                $io->writeln(sprintf('<error>Could not convert: %s</error>', print_r($data, 1)));
            }
        }
        return Command::SUCCESS;
    }

    private function legacy_studip_utf8encode($data)
    {
        if (is_array($data)) {
            $new_data = [];
            foreach ($data as $key => $value) {
                $key = $this->legacy_studip_utf8encode($key);
                $new_data[$key] = $this->legacy_studip_utf8encode($value);
            }
            return $new_data;
        }

        if (!\preg_match('/[\200-\377]/', $data) && !\preg_match("'&#[0-9]+;'", $data)) {
            return $data;
        } else {
            return \mb_decode_numericentity(
                \mb_convert_encoding($data, 'UTF-8', 'WINDOWS-1252'),
                [0x100, 0xffff, 0, 0xffff],
                'UTF-8'
            );
        }
    }
}
