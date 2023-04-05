<?php

namespace Studip\Cli\Commands\Cronjobs;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class CronjobExecute extends Command
{
    protected static $defaultName = 'cronjobs:execute';

    protected function configure(): void
    {
        $this->setDescription('Execute cronjob task.');
        $this->setHelp('This command will execute a cronjob task.');

        $this->addArgument(
            'task_id',
            InputArgument::OPTIONAL,
            'Id of the desired cron job'
        );

        $this->addOption(
            'input',
            'i',
            InputOption::VALUE_NONE,
            'Interactively input values (defaults to true if no task_id is given)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $task_id = $input->getArgument('task_id');

        $input_values = $task_id === null || $input->getOption('input');

        if ($task_id === null) {
            $question = new ChoiceQuestion(
                "\nWhich cronjob should be executed:\n",
                $this->getCronjobTaskList()
            );
            $task_id = $helper->ask($input, $output, $question);
        }

        $task = \CronjobTask::find($task_id);
        if (!$task) {
            $output->writeln('<error>Unknown task id</error>');
            return Command::FAILURE;
        }
        if (!$task->valid) {
            $output->writeln(sprintf(
                '<error>Invalid task, unknown filename %s or invalid class %s</error>',
                $task->filename,
                $task->class
            ));
            return Command::FAILURE;
        }

        $parameters = $this->getDefaultTaskParameters($task);

        if ($input_values && count($parameters) > 0) {
            $output->writeln("\nParameters:\n");

            foreach ($task->parameters as $key => $definition) {
                $description = trim($definition['description'], ' ?');
                $default = trim(json_encode($definition['default'] ?? null), "'");
                $label = " > {$description} [<comment>{$default}</comment>] : ";

                if ($definition['type'] === 'boolean') {
                    $question = new ConfirmationQuestion(
                        $label,
                        $definition['default'],
                        '/^(y|j|1)/i'
                    );
                } elseif ($definition['type'] === 'select' && !empty($definition['values'])) {
                    $question = new ChoiceQuestion(
                        $label,
                        $definition['values']
                    );
                } else {
                    $question = new Question(
                        $label,
                        $definition['default']
                    );
                    if ($definition['type'] === 'integer') {
                        $question->setNormalizer(function ($value) {
                            return $value ? trim($value) : '';
                        })->setValidator(function ($value): int {
                            if (strlen($value) && !ctype_digit($value)) {
                                throw new \RuntimeException('Number is invalid.');
                            }
                            return $value;
                        });
                    }
                }
                $parameters[$key] = $helper->ask($input, $output, $question);
            }
        }

        $task->engage('', $parameters);

        return Command::SUCCESS;
    }

    protected function getCronjobTaskList(): array
    {
        $result = [];
        \CronjobTask::findEachBySQL(
            function (\CronjobTask $task) use (&$result): void
            {
                $result[$task->id] = $task->name;
            },
            '1'
        );
        return $result;
    }

    private function getDefaultTaskParameters(\CronjobTask $task): array
    {
        $parameters = [];
        foreach ($task->parameters as $key => $definition) {
            $parameters[$key] = $definition['default'] ?? null;
        }
        return $parameters;
    }
}
