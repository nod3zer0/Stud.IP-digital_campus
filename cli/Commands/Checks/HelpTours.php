<?php

namespace Studip\Cli\Commands\Checks;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HelpTours extends Command
{
    protected static $defaultName = 'check:helptours';

    protected function configure(): void
    {
        $this->setDescription('Checks help tours for validity.');
        $this->setHelp('This command will check all active help tours if the sites used are still available');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (\HelpTour::findBySQL('1 ORDER BY name ASC') as $tour) {
            if (!$tour->settings->active) {
                if ($output->isVerbose()) {
                    $tour_name = $this->getTourName($tour);
                    $io->info("Skipping inactive tour {$tour_name}");
                }

                continue;
            }

            $errors = [];
            foreach ($tour->steps->orderBy('step ASC') as $step) {
                try {
                    if (match_route('plugins.php/*', $step->route)) {
                        $result = \PluginEngine::routeRequest(substr($step->route, strlen('plugins.php') + 1));

                        // retrieve corresponding plugin info
                        $plugin_manager = \PluginManager::getInstance();
                        $plugin_info = $plugin_manager->getPluginInfo($result[0]);

                        $file = implode('/', [
                            \Config::get()->PLUGINS_PATH,
                            $plugin_info['path'],
                            $plugin_info['class'],
                        ]);

                        if (file_exists($file . '.php')) {
                            $file .= '.php';
                        } elseif (file_exists($file . '.class.php')) {
                            $file .= '.class.php';
                        } else {
                            throw new \Exception();
                        }
                        require_once $file;
                        $plugin = new $plugin_info['class']();

                        if ($result[1]) {
                            $dispatcher = new \Trails_Dispatcher(
                                $GLOBALS['ABSOLUTE_PATH_STUDIP'] . $plugin->getPluginPath(),
                                rtrim(\PluginEngine::getLink($plugin, [], null, true), '/'),
                                'index'
                            );
                            $dispatcher->current_plugin = $plugin;
                            $parsed = $dispatcher->parse($result[1]);
                            $controller = $dispatcher->load_controller($parsed[0]);
                            if ($parsed[1] && !$controller->has_action($parsed[1])) {
                                throw new \Exception();
                            }
                        }
                    } elseif (match_route('dispatch.php/*', $step->route)) {
                        $dispatcher = new \StudipDispatcher();
                        $parsed = $dispatcher->parse(substr($step->route, strlen('dispatch.php') + 1));
                        $controller = $dispatcher->load_controller($parsed[0]);
                        if ($parsed[1] && !$controller->has_action($parsed[1])) {
                            throw new \Exception();
                        }
                    } elseif (!file_exists("{$GLOBALS['ABSOLUTE_PATH_STUDIP']}{$step->route}")) {
                        throw new \Exception();
                    }
                } catch (\Exception $e) {
                    $errors[$step->step] = $step->route;
                }
            }

            if ($errors) {
                $tour_name = $this->getTourName($tour);
                $io->error("{$tour_name} has errors in the following steps:");

                $io->table(
                    ['Step', 'Route'],
                    array_map(
                        function ($step, $route) {
                            return [$step, $route];
                        },
                        array_keys($errors),
                        array_values($errors)
                    )
                );
            }
        }

        return Command::SUCCESS;
    }

    private function getTourName(\HelpTour $tour)
    {
        $type = ucfirst($tour->type);
        return "{$type} '{$tour->name}' ({$tour->language})";
    }
}
