<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class ConsultationSlot extends SchemaProvider
{
    const TYPE = 'consultation-slots';
    const REL_BLOCK = 'block';
    const REL_BOOKINGS = 'bookings';



    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = [
            'note'      => $resource->note,
            'note-html' => formatLinks($resource->note),

            'start_time' => date('c', $resource->start_time),
            'end_time'   => date('c', $resource->end_time),

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
        $relationships = [];
        $relationships = $this->getBlockRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_BLOCK));

        $isPrimary = $context->getPosition()->getLevel() === 0;
        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getBookingsRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_BOOKINGS));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getBlockRelationship($relationships, $resource, $includeData)
    {
        $block = $resource->block;

        $relationships[self::REL_BLOCK] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($block),
            ],
            self::RELATIONSHIP_DATA => $includeData ? $block : \ConsultationBlock::build(['id' => $block->id], false),
        ];

        return $relationships;
    }

    private function getBookingsRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $relatedBookings = $resource->bookings;
        } else {
            $relatedBookings = array_map(function ($booking) {
                return \ConsultationBooking::build(['slot_id' => $booking->slot_id], false);
            }, $resource->bookings);
        }

        $relationships[self::REL_BOOKINGS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BOOKINGS),
            ],
            self::RELATIONSHIP_DATA => $relatedBookings,
        ];

        return $relationships;
    }
}
