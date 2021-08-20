<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class CalendarEvent extends SchemaProvider
{
    const TYPE = 'calendar-events';
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
            'categories' => $resource->toStringCategories(true),
            'location' => $resource->getLocation(),
// TODO: 'is-canceled'    => $singledate->isHoliday() ?: false,

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

        if ($owner = $resource->getOwner()) {
            $link = $this->createLinkToResource($owner);
            $relationships = [
                self::REL_OWNER => [self::RELATIONSHIP_LINKS => [Link::RELATED => $link], self::RELATIONSHIP_DATA => $owner],
            ];
        }

        return $relationships;
    }
}
