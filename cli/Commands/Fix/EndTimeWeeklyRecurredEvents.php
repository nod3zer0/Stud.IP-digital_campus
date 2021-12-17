<?php

namespace Studip\Cli\Commands\Fix;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class EndTimeWeeklyRecurredEvents extends Command
{
    protected static $defaultName = 'fix:end-time-weekly-recurred-events';

    protected function configure(): void
    {
        $this->setDescription('Fix end time weekly recurred events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $events = \EventData::findBySQL("rtype = 'WEEKLY' AND IFNULL(count, 0) > 0");
        $cal_event = new \CalendarEvent();

        $i = 0;

        foreach ($events as $event) {
            $id = $event->getId();
            $cal_event->event = $event;
            $rrule = $cal_event->getRecurrence();
            $cal_event->setRecurrence($rrule);
            $event->expire = $cal_event->event->expire;
            $event->setId($id);
            $event->store();
            $i++;
        }
        $io->info('Wrong end time of recurrence fixed for ' . $i . ' events.');
        return Command::SUCCESS;
    }
}
