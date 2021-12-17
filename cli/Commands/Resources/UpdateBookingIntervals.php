<?php

namespace Studip\Cli\Commands\Resources;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateBookingIntervals extends Command
{
    protected static $defaultName = 'resources:update-booking-intervals';

    protected function configure(): void
    {
        $this->setDescription('Update booking intervals.');
        $this->addOption(
            'remove-exceptions',
            'r',
            InputOption::VALUE_OPTIONAL,
            'exceptions for a booking with repetitions twill be removed',
            false
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $keep_exceptions = $input->getOption('remove-exceptions');
        if ($keep_exceptions !== false) {
            $keep_exceptions = true;
        }
        $bookings = \ResourceBooking::findBySql('TRUE');

        if ($bookings) {
            foreach ($bookings as $booking) {
                $booking->updateIntervals($keep_exceptions);
            }
            $output->writeln('The resource_booking_intervals table is up to date again!');
        } else {
            $output->writeln('There are no bookings in your database! Nothing to do!');
        }
        return Command::SUCCESS;
    }
}
