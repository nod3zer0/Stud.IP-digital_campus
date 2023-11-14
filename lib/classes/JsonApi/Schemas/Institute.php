<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Institute extends SchemaProvider
{
    const TYPE = 'institutes';

    const REL_BLUBBER = 'blubber-threads';
    const REL_FACULTY = 'faculty';
    const REL_FILES = 'file-refs';
    const REL_FOLDERS = 'folders';
    const REL_MEMBERSHIPS = 'memberships';
    const REL_STATUS_GROUPS = 'status-groups';
    const REL_SUB_INSTITUTES = 'sub-institutes';

    /**
     * @param \Institute $institute
     */
    public function getId($institute): ?string
    {
        return $institute->id;
    }

    /**
     * @param \Institute $institute
     */
    public function getAttributes($institute, ContextInterface $context): iterable
    {
        return [
            'name'       => (string) $institute->name,
            'city'       => $institute->plz,
            'street'     => $institute->strasse,
            'phone'      => $institute->telefon,
            'fax'        => $institute->fax,
            'url'        => $institute->url,
            'is-faculty' => $institute->is_fak,
            'mkdate'     => date('c', $institute->mkdate),
            'chdate'     => date('c', $institute->chdate),
        ];
    }

    /**
     * @param \Institute $resource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $relationships[self::REL_FILES] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FILES),
            ],
        ];

        $relationships[self::REL_FOLDERS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FOLDERS),
            ],
        ];

        $relationships[self::REL_BLUBBER] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BLUBBER),
            ],
        ];

        $relationships[self::REL_MEMBERSHIPS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_MEMBERSHIPS),
            ],
        ];

        $relationships = $this->addStatusGroupsRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_STATUS_GROUPS)
        );

        if (!$resource->is_fak) {
            $relationships = $this->addFacultyRelationship(
                $relationships,
                $resource,
                $this->shouldInclude($context, self::REL_FACULTY)
            );
        }

        $relationships = $this->addSubInstitutesRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_SUB_INSTITUTES)
        );

        return $relationships;
    }

    private function addFacultyRelationship(array $relationships, \Institute $resource, bool $includeData): array
    {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_FACULTY),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $resource->faculty;
        } else {
            $relation[self::RELATIONSHIP_DATA] = \Institute::build(['id' => $resource->faculty->id]);
        }

        $relationships[self::REL_FACULTY] = $relation;

        return $relationships;
    }

    private function addSubInstitutesRelationship(array $relationships, \Institute $resource, bool $includeData): array
    {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_SUB_INSTITUTES),
            ],
        ];

        if ($includeData) {
            $relation[self::RELATIONSHIP_DATA] = $resource->sub_institutes;
        } else {
            $relation[self::RELATIONSHIP_DATA] = $resource->sub_institutes->map(function (\Institute $institute): \Institute {
                return \Institute::build(['id' => $institute->id]);
            });
        }

        $relationships[self::REL_SUB_INSTITUTES] = $relation;

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
            $relation[self::RELATIONSHIP_DATA] = $resource->status_groups;
        }

        return array_merge($relationships, [self::REL_STATUS_GROUPS => $relation]);
    }

    public function hasResourceMeta($resource): bool
    {
        return true;
    }

    /**
     * @param \Institute $resource
     */
    public function getResourceMeta($resource)
    {
        return [
            'sub-institutes-count' => count($resource->sub_institutes),
        ];
    }
}
