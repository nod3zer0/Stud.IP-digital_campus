<?php

namespace Studip\Cli\Commands\Course;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Course;

class GetCourse extends Command
{
    protected static $defaultName = 'course:get';

    protected function configure(): void
    {
        $this->setDescription('Get data of Stud.IP courses.');
        $this->setHelp('This command will return the course data.');
        $this->addOption(
            'field',
            'f',
            InputOption::VALUE_OPTIONAL,
            'In which database field should be searched',
            'name'
        );
        $this->addArgument('needle', InputArgument::REQUIRED, 'Value to search for a course.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $needle = $input->getArgument('needle');
        $field = $input->getOption('field');

        if ($field === 'id') {
            $courses = array_filter([Course::find($needle)]);
        } else {
            $courses = Course::findBySQL($field . " LIKE :needle ORDER BY $field", [':needle' => "%$needle%"]);
        }

        if (empty($courses)) {
            $output->writeln('<error>Could not find courses</error>');
            return Command::FAILURE;
        }
        $data = [];

        foreach ($courses as $i =>  $course) {
            $data[] = [
                $course->id,
                $course->veranstaltungsnummer,
                $course->getFullName(),
                $course->getTextualSemester()
            ];
            if ($i + 1 < count($courses)) {
                $data[] = [new TableSeparator(), new TableSeparator()];
            }
        }

        $table = new Table($output);
        $table->setHeaders(['Id', 'Course-number', 'Name', 'Semester'])
            ->setRows($data)
            ->setStyle('box')
            ->render();

        return Command::SUCCESS;
    }
}
