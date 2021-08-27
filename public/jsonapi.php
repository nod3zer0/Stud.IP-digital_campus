<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require '../lib/bootstrap.php';
require '../composer/autoload.php';

\StudipAutoloader::addAutoloadPath($GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR . 'vendor/oauth-php/library/');

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

// Set base url for URLHelper class
URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);

$containerBuilder = new ContainerBuilder();

$settings = require 'lib/classes/JsonApi/settings.php';
$settings($containerBuilder);

$dependencies = require 'lib/classes/JsonApi/dependencies.php';
$dependencies($containerBuilder);

// Build PHP_DI Container
$container = $containerBuilder->build();

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$container->set(\Slim\App::class, $app);

// Set the base path
$app->setBasePath($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP'] . 'jsonapi.php');

// Register middleware
$middleware = require 'lib/classes/JsonApi/middleware.php';
$middleware($app);

// Register routes
$routes = require 'lib/classes/JsonApi/routes.php';
$routes($app);

// Add Error Middleware
$displayErrors = false;
if (defined('\\Studip\\ENV')) {
    $displayErrors = constant('\\Studip\\ENV') === 'development';
}
$logError = true;
$logErrorDetails = true;

$errorMiddleware = $app->addErrorMiddleware($displayErrors, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler(new \JsonApi\Errors\ErrorHandler($app));

// Run app
$app->run();
