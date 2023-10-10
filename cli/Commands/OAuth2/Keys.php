<?php

namespace Studip\Cli\Commands\OAuth2;

use phpseclib3\Crypt\RSA;
use Studip\OAuth2\Container;
use Studip\OAuth2\KeyInformation;
use Studip\OAuth2\SetupInformation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Keys extends Command
{
    protected static $defaultName = 'oauth2:keys';

    protected function configure(): void
    {
        $this->setDescription(
            'Erstelle alle kryptografischen Schlüssel, um Stud.IP als OAuth2-Authorization-Server zu verwenden.'
        );
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Überschreibe ggf. vorhandene Schlüssel');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $container = new Container();
        $setup = $container->get(SetupInformation::class);

        $encryptionKey = $setup->encryptionKey();
        $publicKey = $setup->publicKey();
        $privateKey = $setup->privateKey();

        $force = $input->getOption('force');

        if (($encryptionKey->exists() || $publicKey->exists() || $privateKey->exists()) && !$force) {
            $io->error(
                'Schlüsseldateien liegen bereits vor. Verwenden Sie die Option --force, um diese zu überschreiben.'
            );
            return Command::FAILURE;
        }

        $this->storeKeyContentsToFile($encryptionKey, $this->generateEncryptionKey());

        $keys = RSA::createKey(4096);
        $this->storeKeyContentsToFile($publicKey, $keys->getPublicKey()->toString('PKCS1'));
        $this->storeKeyContentsToFile($privateKey, $keys->toString('PKCS1'));

        $io->info('Schlüsseldateien erfolgreich angelegt.');

        return Command::SUCCESS;
    }

    private function storeKeyContentsToFile(KeyInformation $key, string $contents)
    {
        file_put_contents($key->filename(), $contents);
        chmod($key->filename(), 0660);
    }

    private function generateEncryptionKey(): string
    {
        return "<?php return '" . randomString(48) . "';";
    }
}
