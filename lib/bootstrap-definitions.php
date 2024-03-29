<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    LoggerInterface::class => DI\factory(function () {
        return new Logger('studip', [
            new StreamHandler(
                $GLOBALS['TMP_PATH'] . '/studip.log',
                \Studip\ENV === 'development' ? Logger::DEBUG : Logger::ERROR
            ),
        ]);
    }),
    StudipCache::class => DI\factory(function () {
        return StudipCacheFactory::getCache();
    }),
    StudipPDO::class => DI\factory(function () {
        return DBManager::get();
    }),
    Trails_Dispatcher::class => DI\factory(function (ContainerInterface $container) {
        return new \StudipDispatcher($container);
    }),
];
