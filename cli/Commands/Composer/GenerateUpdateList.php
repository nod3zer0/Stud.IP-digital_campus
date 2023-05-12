<?php
namespace Studip\Cli\Commands\Composer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class GenerateUpdateList extends Command
{
    protected static $defaultName = 'composer:outdated';

    protected function configure(): void
    {
        $this->setDescription('Generate markdown list of outdated packages');
        $this->setHelp('This command will create a markdown list of all outdated packages.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $process = new Process(['composer', 'outdated', '-D', '--locked', '--format=json']);
            $process->mustRun();

            $json = $process->getOutput();
            if ($json) {
                $output->writeln($json, OutputInterface::VERBOSITY_VERBOSE);
            }
        } catch (ProcessFailedException $e) {
            $output->writeln("<error>Could not execute shell command</error>");
            $output->writeln($e->getmessage());
            return Command::FAILURE;
        }

        $list = json_decode($json, true);

        $packages = [
            'major' => [
                'title' => 'Major updates',
                'items' => [],
            ],
            'minor' => [
                'title' => 'Minor updates',
                'items' => [],
            ],
            'abandoned' => [
                'title' => 'Abandoned packages',
                'items' => [],
            ],
        ];

        foreach ($list['locked'] as $package) {
            if ($package['abandoned']) {
                $packages['abandoned']['items'][] = $package;
            } elseif ($package['latest-status'] === 'semver-safe-update') {
                $packages['minor']['items'][] = $package;
            } else {
                $packages['major']['items'][] = $package;
            }
        }


        foreach ($packages as $p) {
            $this->outputMarkdownListOfPackages($output, $p);
        }

        return Command::SUCCESS;
    }

    private function outputMarkdownListOfPackages(
        OutputInterface $output,
        array $packages
    ): void
    {
        if (count($packages['items']) === 0) {
            return;
        }

        $output->writeln("# {$packages['title']}");
        $output->writeln('');

        $headers = [
            'issue'   => 'Issue',
            'name'    => 'Package',
            'version' => 'Installiert',
            'latest'  => 'Verfügbar',
        ];

        $rows = [];
        foreach ($packages['items'] as $package) {
            $name = $package['name'];
            if ($package['homepage']) {
                $name = "[{$name}]({$package['homepage']})";
            } elseif ($package['source']) {
                $name = "[{$name}]({$package['source']})";
            }

            $row = [
                'issue'   => '',
                'name'    => $name,
                'version' => $package['version'],
                'latest'  => $package['latest'],
            ];

            $rows[] = $row;
        }

        $pad_sizes = $this->getPadSizes($headers, ...$rows);

        // Output headers
        $this->outputTableRow($output, $headers, $pad_sizes);

        // Output dividers
        $this->outputTableRow($output, array_fill_keys(array_keys($headers), ''), $pad_sizes, '-');

        // Output all rows
        foreach ($rows as $row) {
            $this->outputTableRow($output, $row, $pad_sizes);
        }

        $output->writeln('');
    }

    private function outputTableRow(OutputInterface $output, array $row, array $pad_sizes = [], string $pad = ' '): void
    {
        $items = [];
        foreach ($row as $key => $value) {
            $items[] = str_pad($value, $pad_sizes[$key] ?? 0, $pad);
        }

        $output->writeln('| ' . implode(' | ', $items) . ' |');
    }

    private function getPadSizes(array ...$rows): array
    {
        $sizes = [];

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                if (!isset($sizes[$key])) {
                    $sizes[$key] = mb_strlen($value);
                } else {
                    $sizes[$key] = max($sizes[$key], mb_strlen($value));
                }
            }
        }

        return $sizes;
    }
}
