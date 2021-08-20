<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class Studip extends SchemaProvider
{
    const TYPE = 'global';

    public function getId($resource): ?string
    {
        return $resource->getId();
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAttributes($news, ContextInterface $context): iterable
    {
        return [];
    }

    public function getRelationships($user, ContextInterface $context): iterable
    {
        return [];
    }
}
