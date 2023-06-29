<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class LtiTool extends SchemaProvider
{
    const TYPE = 'lti-tools';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'name' => $resource->name,
            'launch-url' => $resource->launch_url,
            'allow-custom-url' => (bool) $resource->allow_custom_url,
            'deep-linking' => (bool) $resource->deep_linking,
        ];
    }

    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [];
    }
}
