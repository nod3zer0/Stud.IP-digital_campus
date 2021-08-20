<?php

namespace JsonApi;

use JsonApi\Errors\InternalServerError;
use JsonApi\Middlewares\Authentication;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaInterface;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * TODO.
 */
class NonJsonApiController
{
    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        try {
            $response = $this->invoke($request, $response, $args);
        } catch (JsonApiException $exception) {
            $httpCode = $exception->getHttpCode();
            $errors = $exception->getErrors();
            $reason = count($errors) ? $errors[0]->getTitle() ?? '' : '';

            $response->getBody()->write($reason);
            $response = $response->withStatus($httpCode);
        }

        return $response;
    }

    public function invoke(Request $request, Response $response, array $args): Response
    {
        throw new InternalServerError();
    }

    /**
     * @return mixed
     */
    protected function getUser(Request $request)
    {
        return $request->getAttribute(Authentication::USER_KEY, null);
    }

    /**
     * Gibt das Schema zu einer beliebigen Ressource zurück.
     *
     * @param mixed $resource die Ressource, zu der das Schema geliefert werden soll
     *
     * @return SchemaInterface das Schema zur Ressource
     */
    protected function getSchema($resource): SchemaInterface
    {
        return $this->container->get(SchemaContainerInterface::class)->getSchema($resource);
    }
}
