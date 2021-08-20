<?php
namespace JsonApi;

use Slim\App;

return function (App $app) {
    $app->group('/v1', new RouteMap($app));
};
