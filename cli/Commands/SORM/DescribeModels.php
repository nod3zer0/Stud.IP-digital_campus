<?php
namespace Studip\Cli\Commands\SORM;

use SimpleORMapCollection;
use Studip\Cli\Commands\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DescribeModels extends AbstractCommand
{
    protected static $defaultName = 'sorm:describe';

    private $progress;
    private $reflection;

    protected function configure(): void
    {
        $this->setDescription('Describe models');
        $this->setHelp('This command will add neccessary @property annotations to SimpleORMap classes in a folder');

        $this->addArgument(
            'folder',
            InputArgument::OPTIONAL,
            'Folder to scan (will default to lib/models)',
            'lib/models'
        );

        $this->addOption(
            'recursive',
            'r',
            InputOption::VALUE_NEGATABLE,
            'Scan into subfolders recursively'
        );
        $this->addOption(
            'bootstrap',
            'b',
            InputOption::VALUE_OPTIONAL,
            'Execute bootstrap file before scanning folder'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $bootstrap = $input->getOption('bootstrap');
        if ($bootstrap) {
            if (!file_exists($bootstrap)) {
                throw new \Exception("Invalid bootstrap file {$bootstrap} provided");
            }
            require_once $bootstrap;
        }

        $recursive = $input->getOption('recursive') ?? false;
        $folder = $input->getArgument('folder');
        $iterator = $this->getFolderIterator($folder, $recursive, ['php']);

        $this->progress = new ProgressBar($output, iterator_count($iterator));
        $this->progress->start();

        foreach ($iterator as $file) {
            $filename = $file->getFilename();

            if (!is_writable($file->getRealPath())) {
                $this->outputForFile(
                    $output,
                    '<comment>Skipping not writable file ' . $this->relativeFilePath($file->getPathname()) . '</comment>'
                );
                continue;
            }

            $class_name = str_replace('.class.php', '.php', $filename);
            $class_name = pathinfo($class_name, PATHINFO_FILENAME);
            if (!class_exists($class_name)) {
                $class_name = $this->getClassNameFromFile($file->getPathname()) ?? $class_name;
            }

            if (!class_exists($class_name) || !is_subclass_of($class_name, \SimpleORMap::class)) {
                $this->outputForFile(
                    $output,
                    "Skipping invalid class file {$filename} (class {$class_name})",
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }

            try {
                $this->reflection = new \ReflectionClass($class_name);
            } catch (\Error $e) {
                $this->outputForFile(
                    $output,
                    "<error>Could not get reflection for class {$class_name} ({$e->getMessage()})</error>"
                );
                continue;
            }

            if ($this->reflection->isAbstract()) {
                $this->outputForFile(
                    $output,
                    "Skipping abstract class {$class_name}",
                    OutputInterface::VERBOSITY_VERBOSE
                );
                continue;
            }


            $model = $this->reflection->newInstance();

            // Get configuration for class
            $config_property = $this->reflection->getProperty('config');
            $config_property->setAccessible(true);
            $model_config = $config_property->getValue()[$class_name];

            // Get table metadata
            $meta = $model->getTableMetaData();

            $properties = [];

            if (!isset($meta['fields']['id']) && count($meta['pk']) > 0) {
                $properties['id'] = [
                    'type' => count($meta['pk']) > 1 ? 'array' : $this->getPHPType($meta['pk'][0], $meta['fields'][$meta['pk'][0]]),
                    'description' => 'alias for pk',
                ];
            }

            foreach ($meta['fields'] as $field => $info) {
                $name = mb_strtolower($field);
                $type = $this->getPHPType($field, $info, $model_config);
                $properties[$name] = [
                    'type'        => $type,
                    'description' => 'database column',
                ];

                $alias = array_search($name, $meta['alias_fields']);
                if ($alias) {
                    $properties[$alias] = [
                        'type'        => $type,
                        'description' => "alias column for {$name}",
                    ];
                }
            }

            foreach ($meta['relations'] as $relation) {
                $options = $model->getRelationOptions($relation);
                $related_class_name = $options['class_name'];

                if (in_array($options['type'], ['has_many', 'has_and_belongs_to_many'])) {
                    $related_type = implode('|', [
                        $this->adjustNamespaceForClass(SimpleORMapCollection::class),
                        $this->adjustNameSpaceForClass($related_class_name) . '[]',
                    ]);
                } else {
                    $related_type = $this->adjustNamespaceForClass($related_class_name);

                    if (
                        $options['foreign_key'] !== 'id'
                        && isset($meta['fields'][$options['foreign_key']])
                        && $meta['fields'][$options['foreign_key']]['null'] === 'YES'
                    ) {
                        $related_type .= '|null';
                    }
                }

                $properties[$relation] = [
                    'type'        => $related_type,
                    'description' => "{$options['type']} " . $this->adjustNamespaceForClass($related_class_name),
                ];
            }

            foreach ($meta['additional_fields'] as $field => $definition) {
                if ($field === 'id') {
                    continue;
                }

                $property_type = null;
                if (isset($definition['get']) && !isset($definition['set'])) {
                    $property_type = 'property-read';
                } elseif (!isset($definition['get']) && isset($definition['set'])) {
                    $property_type = 'property-write';
                }

                $properties[$field] = [
                    'property_type' => $property_type,
                    'type' => $this->getAdditionFieldType($definition),
                    'description' => 'additional field',
                ];
            }

            if ($this->updateDocBlockOfClass($properties)) {
                $this->outputForFile(
                    $output,
                    '<info>Updated ' . $this->relativeFilePath($file->getPathname()) . '</info>'
                );
            } else {
                $this->outputForFile(
                    $output,
                    'No changes in ' . $this->relativeFilePath($file->getPathname()),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }

        $this->progress->clear();

        return Command::SUCCESS;
    }

    private function outputForFile($output, ...$args)
    {
        $this->progress->advance();
        $this->progress->clear();
        $output->writeln(...$args);
        $this->progress->display();

    }

    private function getPHPType(string $field, array $info, array $config = []): string
    {
        $field = strtolower($field);

        $type = [];

        if (isset($config['serialized_fields'][$field])) {
            $type[] = $this->adjustNamespaceForClass($config['serialized_fields'][$field]);
        } elseif (isset($config['i18n_fields'][$field])) {
            $type[] = $this->adjustNamespaceForClass(\I18NString::class);
        } elseif (preg_match('/^(?:tiny|small|medium|big)?int(?:eger)?/i', $info['type'])) {
            $type[] = 'int';
        } elseif (preg_match('/^(?:decimal|double|float|numeric)/i', $info['type'])) {
            $type[] = 'float';
        } else {
            $type[] = 'string';
        }

        if ($info['null'] === 'YES') {
            $type[] = 'null';
        }

        return implode('|', $type);
    }

    private function updateDocBlockOfClass(array $properties): bool
    {
        $has_docblock = (bool) $this->reflection->getDocComment();
        $docblock     = $this->reflection->getDocComment() ?: $this->getDefaultDocblock();

        $docblock_lines = array_map('rtrim', explode("\n", $docblock));

        $properties_started = false;
        $docblock_lines = array_filter($docblock_lines, function ($line) use (&$properties_started) {
            $line = ltrim($line, '* ');
            if ($properties_started) {
                return $line === '/';
            }

            $properties_started = strpos($line, '@property ') === 0;
            return !$properties_started;
        });

        $docblock_lines = array_reverse($docblock_lines);
        while ($docblock_lines[1] === ' *') {
            array_splice($docblock_lines, 1, 1);
        }
        $docblock_lines = array_reverse($docblock_lines);

        $properties = array_map(function ($variable, $property) {
            return sprintf(
                ' * @%s %s $%s %s',
                $property['property_type'] ?? 'property',
                $property['type'],
                $variable,
                $property['description']
            );
        }, array_keys($properties), array_values($properties));

        array_unshift($properties, ' *');
        array_splice($docblock_lines, -1, 0, $properties);

        $new_docblock = implode("\n", $docblock_lines);

        if ($docblock === $new_docblock) {
            return false;
        }

        $contents = file_get_contents($this->reflection->getFileName());
        if ($has_docblock) {
            $contents = str_replace($docblock, $new_docblock, $contents);
        } else {
            $contents = preg_replace(
                '/^class/m',
                $new_docblock . "\nclass",
                $contents,
                1
            );
        }

        file_put_contents($this->reflection->getFileName(), $contents);

        return true;
    }

    private function getDefaultDocBlock(): string
    {
        return implode("\n", [
            '/**',
            ' * @license GPL2 or any later version',
            ' */',
        ]);
    }

    /**
     * @see https://stackoverflow.com/a/14250011
     */
    private function getClassNameFromFile(string $filename): ?string
    {
        $code = file_get_contents($filename);
        $tokens = token_get_all($code);

        $namespace = '';

        for ($i = 0, $l = count($tokens); $i < $l; $i += 1) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j= $i + 1; $j < $l; $j += 1) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= "\\" . $tokens[$j][1];
                    } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                }
            }
            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < $l; $j += 1) {
                    if ($tokens[$j] === '{') {
                        return ltrim($namespace . "\\" . $tokens[$i + 2][1], "\\");
                    }
                }
            }
        }
        return null;
    }

    private function adjustNamespaceForClass(string $class): string
    {
        if (!$this->reflection->inNamespace()) {
            return $class;
        }

        $namespace = $this->reflection->getNamespaceName();
        $class = "\\{$class}";
        if (str_starts_with($class, "\\{$namespace}")) {
            $class = substr($class, strlen($namespace) + 2);
        }
        return $class;
    }

    private function getAdditionFieldType($definition): string
    {
        // TODO: Try to get a reasonable field type
        // $definition may either be an array with definition or a boolean value

        return 'mixed';
    }
}
