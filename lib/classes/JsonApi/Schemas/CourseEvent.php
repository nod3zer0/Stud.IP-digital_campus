<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class CourseEvent extends SchemaProvider
{
    const TYPE = 'course-events';
    const REL_OWNER = 'owner';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'title' => isset($resource->course) ? $resource->course->getFullName() : '',
            'description' => $resource->getDescription(),
            'start' => date('c', $resource->date),
            'end' => date('c', $resource->end_time),
            'categories' => '',
            'location' => $resource->raum ?? '',
            'is-cancelled' => $resource instanceof \CourseExDate,
            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
            'recurrence' => isset($resource->cycle) ? $resource->cycle->toString() : '',
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        if ($owner = $resource->course) {
            $link = $this->createLinkToResource($owner);
            $relationships = [
                self::REL_OWNER => [self::RELATIONSHIP_LINKS => [Link::RELATED => $link], self::RELATIONSHIP_DATA => $owner],
            ];
        }

        return $relationships;
    }
}
