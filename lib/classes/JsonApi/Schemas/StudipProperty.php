<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class StudipProperty extends SchemaProvider
{
    const TYPE = 'studip-properties';

    public function getId($resource): ?string
    {
        return $resource->field;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'description' => $resource->description,
            'value' => $resource->value,
        ];
    }

    public function getRelationships($resource, ContextInterface $context): iterable
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
