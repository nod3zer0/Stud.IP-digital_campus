<?php

namespace JsonApi;

use DI\ContainerBuilder;
use JsonApi\JsonApiIntegration\QueryParser;
use JsonApi\JsonApiIntegration\QueryParserInterface;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Factories\FactoryInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\HeaderParametersParserInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Neomerx\JsonApi\Contracts\Http\ResponsesInterface;
use Neomerx\JsonApi\Contracts\Schema\SchemaContainerInterface;
use Neomerx\JsonApi\Http\Headers\HeaderParametersParser;
use Neomerx\JsonApi\Http\Headers\MediaType;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        FactoryInterface::class => \DI\create(JsonApiIntegration\Factory::class),
        HeaderParametersParserInterface::class => function (FactoryInterface $factory) {
            return new HeaderParametersParser($factory);
        },

        SchemaContainerInterface::class => function (ContainerInterface $container, FactoryInterface $factory) {
            $schemas = [];
            $user = $container->get('studip-current-user');
            foreach ($container->get('json-api-integration-schemas') as $key => $classname) {
                $schemas[$key] = function ($schemaContainer) use ($classname, $factory, $user) {
                    return new $classname($factory, $schemaContainer, $user);
                };
            }

            return $factory->createSchemaContainer($schemas);
        },

        EncoderInterface::class => function (
            ContainerInterface $container,
            FactoryInterface $factory,
            SchemaContainerInterface $schemaContainer
        ) {
            $urlPrefix = $container->get('json-api-integration-urlPrefix');
            $encoder = $factory->createEncoder($schemaContainer)->withUrlPrefix($urlPrefix);

            return $encoder;
        },

        QueryParserInterface::class => function (ContainerInterface $container) {
            $request = $container->get('request');
            $parameters = $request->getQueryParams();
            $queryParser = new QueryParser($parameters);

            return $queryParser;
        },

        ResponsesInterface::class => function (EncoderInterface $encoder) {
            $mediaType = new MediaType(MediaTypeInterface::JSON_API_TYPE, MediaTypeInterface::JSON_API_SUB_TYPE);

            return new JsonApiIntegration\Responses($encoder, $mediaType);
        },
    ]);
};
