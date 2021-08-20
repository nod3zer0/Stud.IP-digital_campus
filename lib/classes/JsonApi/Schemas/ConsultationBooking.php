<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class ConsultationBooking extends SchemaProvider
{
    const TYPE = 'consultation-bookings';
    const REL_SLOT = 'slot';
    const REL_USER = 'user';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = [
            'reason' => $resource->reason,

            'mkdate' => date('c', $resource->mkdate),
            'chdate' => date('c', $resource->chdate),
        ];

        return $attributes;
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];
        $relationships = $this->getSlotRelationship($relationships, $resource, $shouldInclude(self::REL_SLOT));
        $relationships = $this->getUserRelationship($relationships, $resource, $shouldInclude(self::REL_USER));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getSlotRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        $slot = $resource->slot;

        $relationships[self::REL_SLOT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_SLOT),
            ],
            self::RELATIONSHIP_DATA => $includeData ? $slot : \ConsultationSlot::build(['id' => $slot->id], false),
        ];

        return $relationships;
    }

    private function getRangeRelationship($relationships, $resource, $includeData)
    {
        $user = $resource->user;

        $relationships[self::REL_USER] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_USER),
            ],
            self::RELATIONSHIP_DATA => $includeData ? $user : \User::build(['id' => $user->id], false),
        ];

        return $relationships;
    }
}
