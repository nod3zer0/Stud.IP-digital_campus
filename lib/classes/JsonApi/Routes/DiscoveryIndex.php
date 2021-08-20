<?php

namespace JsonApi\Routes;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\JsonApiController;

class DiscoveryIndex extends JsonApiController
{
    public function __invoke(Request $request, Response $response)
    {
        $routes = $this->container->get(\Slim\App::class)->getRouteCollector()->getRoutes();

        return $this->getContentResponse($routes);
    }
}
