<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class ScheduleEntry extends SchemaProvider
{
    const TYPE = 'schedule-entries';
    const REL_OWNER = 'owner';



    public function getId($entry): ?string
    {
        return $entry->id;
    }

    public function getAttributes($entry, ContextInterface $context): iterable
    {
        return [
            'title' => $entry->title,
            'description' => mb_strlen(trim($entry->content)) ? $entry->content : null,

            'start' => $this->formatTime($entry->start),
            'end' => $this->formatTime($entry->end),
            'weekday' => (int) $entry->day,

            'color' => $entry->color,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($entry, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $link = $this->createLinkToResource($entry->user);

        $relationships = [
            self::REL_OWNER => [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $link,
                ],
                self::RELATIONSHIP_DATA => $entry->user,
            ],
        ];

        return $relationships;
    }

    private function formatTime($time)
    {
        return sprintf('%02d:%02d', (int) ($time / 100), (int) ($time % 100));
    }
}
