<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

/**
 * Diese Klasse definiert eine Middleware, die Requests  umleitet,
 * die mit einem Schrägstrich enden (und zwar jeweils auf das Pendant
 * ohne Schrägstrich).
 */
class RemoveTrailingSlashes
{
    /**
     * Diese Middleware überprüft den Pfad der URI des Requests. Endet
     * diese auf einem Schrägstrich, wird nicht weiter an `$next`
     * delegiert, sondern eine Response mit `Location`-Header also
     * einem Redirect zurückgegeben.
     *
     * @param Request        $request das Request-Objekt
     * @param RequestHandler $handler der PSR-15 Request Handler
     *
     * @return ResponseInterface das neue Response-Objekt
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' != $path && '/' == substr($path, -1)) {
            // recursively remove slashes when its more than 1 slash
            $path = rtrim($path, '/');

            // permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath($path);

            if ('GET' == $request->getMethod()) {
                $response = new Response();

                return $response
                    ->withHeader('Location', (string) $uri)
                    ->withStatus(301);
            } else {
                $request = $request->withUri($uri);
            }
        }

        return $handler->handle($request);
    }
}
