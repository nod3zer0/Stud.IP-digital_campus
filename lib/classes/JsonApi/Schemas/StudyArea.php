<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class StudyArea extends SchemaProvider
{
    const REL_CHILDREN = 'children';
    const REL_COURSES = 'courses';
    const REL_INSTITUTE = 'institute';
    const REL_PARENT = 'parent';
    const TYPE = 'study-areas';

    public function getId($resource): ?string
    {
        return $resource['id'];
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'name' => (string) $resource['name'],
            'info' => (string) $resource['info'],
            'priority' => (int) $resource['priority'],
            'type-name' => (string) $resource->getTypeName(),
        ];
    }

    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $relationships = [];

        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = $this->addChildrenRelationship($relationships, $resource, $shouldInclude(self::REL_CHILDREN));
        $relationships = $this->addCoursesRelationship($relationships, $resource, $shouldInclude(self::REL_COURSES));
        $relationships = $this->addInstituteRelationship($relationships, $resource, $shouldInclude(self::REL_INSTITUTE));
        $relationships = $this->addParentRelationship($relationships, $resource, $shouldInclude(self::REL_PARENT));

        return $relationships;
    }

    private function addChildrenRelationship(array $relationships, $resource, $includeData)
    {
        $relationships[self::REL_CHILDREN] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CHILDREN),
            ],
        ];

        if ($includeData) {
            $children = $resource->getChildren();
            $relationships[self::REL_CHILDREN][self::RELATIONSHIP_DATA] = $children;
        }

        return $relationships;
    }

    private function addCoursesRelationship(array $relationships, $resource, $includeData)
    {
        $relationships[self::REL_COURSES] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_COURSES),
            ],
        ];

        if ($includeData) {
            $children = $resource->courses;
            $relationships[self::REL_COURSES][self::RELATIONSHIP_DATA] = $children;
        }

        return $relationships;
    }

    private function addInstituteRelationship(array $relationships, $resource, $includeData)
    {
        $relationships[self::REL_INSTITUTE] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_INSTITUTE),
            ],
        ];

        if ($includeData) {
            $relationships[self::REL_INSTITUTE][self::RELATIONSHIP_DATA] = $resource->institute;
        }

        return $relationships;
    }

    private function addParentRelationship(array $relationships, $resource, $includeData)
    {
        $relationships[self::REL_PARENT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_PARENT),
            ],
        ];

        if ($includeData) {
            $relationships[self::REL_PARENT][self::RELATIONSHIP_DATA] = $resource->getParent();
        }

        return $relationships;
    }
}
