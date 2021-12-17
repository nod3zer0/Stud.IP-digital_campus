<?php

namespace Studip\Cli\Commands\HelpContent;

use DBManager;
use PDO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Migrate extends Command
{
    protected static $defaultName = 'help-content:migrate';

    protected function configure(): void
    {
        $this->setDescription('Migrate help-content.');
        $this->addArgument('version', InputArgument::REQUIRED, 'Version of the help content');
        $this->addArgument('language', InputArgument::REQUIRED, 'Language of the help content');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $version = $input->getArgument('version');
        $language = $input->getArgument('language');
        $help_content_path = $GLOBALS['STUDIP_BASE_PATH'] . '/doc/helpbar';

        $query = 'SELECT * FROM help_content WHERE studip_version = ? LIMIT 1';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$version]);
        $ret = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        if ($ret && count($ret)) {
            $io->info('Helpbar content already present for this version!');
            return Command::SUCCESS;
        }

        $filename = $help_content_path . '/' . $language . '/helpcontent.json';

        if (!is_file($filename)) {
            $io->error('File not found: ' . $filename);
            return Command::FAILURE;
        }

        $json = json_decode(file_get_contents($filename), true);

        if ($json === null) {
            $io->error('Helpbar content could not be loaded. File: ' . $filename);
            return Command::FAILURE;
        }

        $count = [];
        foreach ($json as $row) {
            if (!is_array($row['text'])) {
                $row['text'] = [$row['text']];
            }
            if (!$row['label'] || !$row['icon']) {
                $row['label'] = '';
            }
            $installation_id = \Config::get()->STUDIP_INSTALLATION_ID;
            foreach ($row['text'] as $index => $text) {
                $count[$language . $row['route']]++;
                $statement = DBManager::get()->prepare(
                    "INSERT INTO help_content (
                          `content_id`,
                          `language`,
                          `label`,
                          `icon`,
                          `content`,
                          `route`,
                          `studip_version`,
                          `position`,
                          `custom`,
                          `visible`,
                          `author_id`,
                          `installation_id`,
                          `mkdate`
                      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 1, '', ?, UNIX_TIMESTAMP())"
                );
                $statement->execute([
                    md5(uniqid(rand(), true)),
                    $language,
                    $index == 0 ? $row['label'] : '',
                    $index == 0 ? $row['icon'] : '',
                    $text,
                    $row['route'],
                    $version,
                    $count[$language . $row['route']],
                    $installation_id,
                ]);
            }
        }

        if (count($count)) {
            if (!\Config::get()->getValue('HELP_CONTENT_CURRENT_VERSION')) {
                \Config::get()->create('HELP_CONTENT_CURRENT_VERSION', [
                    'value' => $version,
                    'is_default' => 0,
                    'type' => 'string',
                    'range' => 'global',
                    'section' => 'global',
                    'description' => _('Aktuelle Version der Helpbar-Einträge in Stud.IP'),
                ]);
            } else {
                \Config::get()->store('HELP_CONTENT_CURRENT_VERSION', $version);
            }
        }
        $io->success('help content added for ' . count($count) . ' routes.');
        return Command::SUCCESS;
    }
}
