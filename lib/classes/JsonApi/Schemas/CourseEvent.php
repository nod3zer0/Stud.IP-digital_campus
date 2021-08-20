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
            'title' => $resource->title,
            'description' => $resource->getDescription(),
            'start' => date('c', $resource->getStart()),
            'end' => date('c', $resource->getEnd()),
            'categories' => array_filter($resource->toStringCategories(true)),
            'location' => $resource->getLocation(),

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
            'recurrence' => $resource->getRecurrence(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

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
