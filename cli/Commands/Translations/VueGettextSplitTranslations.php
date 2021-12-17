<?php

namespace Studip\Cli\Commands\Translations;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VueGettextSplitTranslations extends Command
{
    protected static $defaultName = 'translations:vue-gettext-split';

    protected function configure(): void
    {
        $this->setDescription('Split vue-gettext.');
        $this->setHelp('Split vue-gettext translations');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $translationsFile = $GLOBALS['STUDIP_BASE_PATH'] . '/resources/locales/translations.json';
        if (file_exists($translationsFile)) {
            $file = file_get_contents($translationsFile);
            $json = json_decode($file, true);
            foreach ($json as $lang => $content) {
                $langFile = realpath(__DIR__ . '/../resources/locales/') . '/' . $lang . '.json';
                file_put_contents($langFile, json_encode($content));
            }
            return Command::SUCCESS;
        } else {
            $output->writeln(sprintf('<error>Could not find translations in %s</error>', $translationsFile));
            return Command::FAILURE;
        }
    }
}
