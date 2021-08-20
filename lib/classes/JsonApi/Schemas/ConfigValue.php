<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class ConfigValue extends SchemaProvider
{
    const TYPE = 'config-values';

    public function getId($resource): ?string
    {
        return join('_', [$resource['range_id'], $resource['field']]);
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $i18nAwareCast = function ($mixed) use ($resource) {
            return 'i18n' === $resource->entry['type'] ? (string) $mixed : $mixed;
        };

        return [
            'field' => $resource['field'],
            'value' => $i18nAwareCast($resource->getTypedValue()),
            'field-type' => $resource->entry['type'],
            'comment' => $resource['comment'],
            'default-value' => $i18nAwareCast($resource->getTypedDefaultValue()),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    public function getRelationships($user, ContextInterface $context): iterable
    {
        return [];
    }
}
