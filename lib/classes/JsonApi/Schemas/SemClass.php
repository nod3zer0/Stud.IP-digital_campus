<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class SemClass extends SchemaProvider
{
    const REL_SEM_TYPES = 'sem-types';
    const TYPE = 'sem-classes';

    public function getId($resource): ?string
    {
        return $resource['id'];
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'name' => (string) $resource['name'],
            'only-inst-user' => (bool) $resource['only_inst_user'],
            'default-read-level' => (int) $resource['default_read_level'],
            'default-write-level' => (int) $resource['default_write_level'],
            'bereiche' => (int) $resource['bereiche'],
            'show-browse' => (bool) $resource['show_browse'],
            'write-access-nobody' => (bool) $resource['write_access_nobody'],
            'topic-create-autor' => (bool) $resource['topic_create_autor'],
            'visible' => (bool) $resource['visible'],
            'course-creation-forbidden' => (bool) $resource['course_creation_forbidden'],
        ];
    }

    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $relationships = $this->addSemTypesRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_SEM_TYPES)
        );

        return $relationships;
    }

    private function addSemTypesRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_SEM_TYPES),
            ],
        ];

        if ($includeData) {
            $related = $resource->getSemTypes();
            $relation[self::RELATIONSHIP_DATA] = $related;
        }

        $relationships[self::REL_SEM_TYPES] = $relation;

        return $relationships;
    }
}
