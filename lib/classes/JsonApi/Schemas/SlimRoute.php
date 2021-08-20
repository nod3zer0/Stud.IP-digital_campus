<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class SlimRoute extends SchemaProvider
{
    const TYPE = 'slim-routes';

    public function getId($route): ?string
    {
        return $route->getIdentifier();
    }

    public function getAttributes($route, ContextInterface $context): iterable
    {
        return [
            'methods' => $route->getMethods(),
            'name' => $route->getName(),
            'pattern' => $route->getPattern(),
        ];
    }

    public function getRelationships($user, ContextInterface $context): iterable
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getLinks($resource): iterable
    {
        return [];
    }
}
