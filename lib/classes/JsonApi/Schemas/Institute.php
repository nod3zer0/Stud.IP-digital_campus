<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Institute extends SchemaProvider
{
    const TYPE = 'institutes';

    const REL_BLUBBER = 'blubber-threads';
    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';
    const REL_STATUS_GROUPS = 'status-groups';

    public function getId($institute): ?string
    {
        return $institute->id;
    }

    public function getAttributes($institute, ContextInterface $context): iterable
    {
        return [
            'name' => $institute['Name'],
            'city' => $institute['Plz'],
            'street' => $institute['Strasse'],
            'phone' => $institute['telefon'],
            'fax' => $institute['fax'],
            'url' => $institute['url'],
            'mkdate' => date('c', $institute['mkdate']),
            'chdate' => date('c', $institute['chdate']),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $filesLink = $this->getRelationshipRelatedLink($resource, self::REL_FILES);
        $relationships[self::REL_FILES] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $filesLink,
            ],
        ];

        $foldersLink = $this->getRelationshipRelatedLink($resource, self::REL_FOLDERS);
        $relationships[self::REL_FOLDERS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $foldersLink,
            ],
        ];

        $relationships[self::REL_BLUBBER] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BLUBBER),
            ],
        ];

        $relationships = $this->addStatusGroupsRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_STATUS_GROUPS)
        );

        return $relationships;
    }

    private function addStatusGroupsRelationship(
        array $relationships,
        $resource,
        $includeData
    ) {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_STATUS_GROUPS),
            ]
        ];
        if ($includeData) {
            $related = $resource->status_groups;
            $relation[self::RELATIONSHIP_DATA] = $related;
        }

        return array_merge($relationships, [self::REL_STATUS_GROUPS => $relation]);
    }
}
