<?php

namespace Studip\Cli\Commands\OAuth2;

use Studip\OAuth2\Models\AccessToken;
use Studip\OAuth2\Models\AuthCode;
use Studip\OAuth2\Models\RefreshToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Purge extends Command
{
    protected static $defaultName = 'oauth2:purge';

    protected function configure(): void
    {
        $this->setDescription('Bereinige die OAuth2-Datenbanktabellen von widerrufenen und/oder abgelaufenen Token');
        $this->addOption('revoked', null, InputOption::VALUE_NONE, 'Entferne widerrufene Token');
        $this->addOption('expired', null, InputOption::VALUE_NONE, 'Entferne abgelaufene Token');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $expiryDate = strtotime('-7days midnight');

        $revoked = $input->getOption('revoked');
        $expired = $input->getOption('expired');

        if (($revoked && $expired) || (!$revoked && !$expired)) {
            AccessToken::deleteBySQL('revoked = ? AND expires_at < ?', [1, $expiryDate]);
            AuthCode::deleteBySQL('revoked = ? AND expires_at < ?', [1, $expiryDate]);
            RefreshToken::deleteBySQL('revoked = ? AND expires_at < ?', [1, $expiryDate]);

            $io->info(
                'Alle Token, die widerrufen wurden oder vor mindestens 7 Tagen abgelaufen sind, wurden entfernt.'
            );
        } elseif ($revoked) {
            AccessToken::deleteBySQL('revoked = ?', [1]);
            AuthCode::deleteBySQL('revoked = ?', [1]);
            RefreshToken::deleteBySQL('revoked = ?', [1]);

            $io->info('Alle widerrufenen Token wurden entfernt.');
        } elseif ($expired) {
            AccessToken::deleteBySQL('expires_at < ?', [$expiryDate]);
            AuthCode::deleteBySQL('expires_at < ?', [$expiryDate]);
            RefreshToken::deleteBySQL('expires_at < ?', [$expiryDate]);

            $io->info('Alle Token, die vor mindestens 7 Tagen abgelaufen sind, wurden entfernt.');
        }

        return Command::SUCCESS;
    }
}
