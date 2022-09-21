<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class ContentTermsOfUse extends SchemaProvider
{
    const TYPE = 'terms-of-use';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'name' => (string) $resource['name'],
            'description' => mb_strlen($resource['description']) ? (string) $resource['description'] : null,
            'icon' => $resource['icon'],
            'is-default' => (bool) $resource['is_default'],
            'download-condition' => (int) $resource['download_condition'],
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    public function getRelationships($user, ContextInterface $context): iterable
    {
        return [];
    }
}
