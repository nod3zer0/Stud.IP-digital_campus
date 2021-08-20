<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * TODO.
 */
class DangerousRouteHandler
{
    /**
     * TODO.
     *
     * @param Request        $request das Request-Objekt
     * @param RequestHandler $handler der PSR-15 Request Handler
     *
     * @return ResponseInterface das neue Response-Objekt
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        if (\Config::get()->getValue('JSONAPI_DANGEROUS_ROUTES_ALLOWED')) {
            return $handler->handle($request);
        }
        $response = new Response();

        return $response->withStatus(503)->write('Service Unavailable.');
    }
}
