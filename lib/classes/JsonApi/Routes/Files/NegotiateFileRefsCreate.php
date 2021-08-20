<?php

namespace JsonApi\Routes\Files;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NegotiateFileRefsCreate
{
    /** @var ContainerInterface */
    private $container;

    /**
     * Der Konstruktor.
     *
     * @param ContainerInterface $container der Dependency Container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $contentType = $request->getHeaderLine('Content-Type');
        if ('multipart/form-data' === substr($contentType, 0, strlen('multipart/form-data'))) {
            $route = $this->container->get(FileRefsCreateByUpload::class);
        } else {
            $route = $this->container->get(FileRefsCreate::class);
        }

        return $route($request, $response, $args);
    }
}
