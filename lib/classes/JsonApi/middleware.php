<?php
namespace JsonApi;

use Slim\App;

return function (App $app) {
    /** @var \DI\Container */
    $container = $app->getContainer();

    $app->addBodyParsingMiddleware([
        'application/vnd.api+json' => function ($input) {
            return json_decode($input, true);
        },
    ]);

    // set 'request' in container
    $app->add(function ($request, $handler) use ($container) {
        $container->set('request', $request);

        return $handler->handle($request);
    });

    $app->add(new Middlewares\StudipMockNavigation());
    $app->add(new Middlewares\RemoveTrailingSlashes());

    // Add Routing Middleware
    $app->addRoutingMiddleware();

    // Add language middleware
    $app->add(new Middlewares\Language());

    /** @var array|null */
    $corsOrigin = \Config::get()->getValue('JSONAPI_CORS_ORIGIN');
    if (is_array($corsOrigin) && count($corsOrigin)) {
        $app->add(
            new \Tuupola\Middleware\CorsMiddleware([
                'origin' => $corsOrigin,
                'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
                'headers.allow' => [
                    'Accept',
                    'Accept-Encoding',
                    'Accept-Language',
                    'Authorization',
                    'Content-Type',
                    'Origin',
                ],
                'headers.expose' => ['Etag'],
                'credentials' => true,
                'cache' => 86400,
            ])
        );
    }
};
