<?php

namespace JsonApi\Schemas;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Contracts\Schema\LinkInterface;

class StockImage extends SchemaProvider
{
    public const TYPE = 'stock-images';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        return $resource->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'title' => $resource['title'],
            'description' => $resource['description'],
            'author' => $resource['author'],
            'license' => $resource['license'],
            'mime-type' => $resource['mime_type'],

            'download-urls' => $resource->getDownloadURLs(),

            'size' => (int) $resource['size'],
            'width' => (int) $resource['width'],
            'height' => (int) $resource['height'],
            'palette' => empty($resource['palette']) ? null : json_decode($resource['palette']),
            'tags' => empty($resource['tags']) ? [] : json_decode($resource['tags']),

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        return [];
    }
}
