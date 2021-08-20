<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class ConsultationBlock extends SchemaProvider
{
    const TYPE = 'consultation-blocks';
    const REL_SLOTS = 'slots';
    const REL_RANGE = 'range';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = [
            'start' => date('c', $resource->start),
            'end'   => date('c', $resource->end),

            'room' => $resource->room,
            'size' => (int) $resource->size,

            'show-participants' => (bool) $resource->show_participants,
            'require-reason'    => $resource->require_reason,

            'confirmation-text'      => $resource->confirmation_text ?: null,
            'confirmation-text-html' => formatLinks($resource->confirmation_text) ?: null,

            'note'      => $resource->note,
            'note-html' => formatLinks($resource->note),

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
        $relationships = $this->getSlotsRelationship($relationships, $resource, $shouldInclude(self::REL_SLOTS));

        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getRangeRelationship($relationships, $resource, $shouldInclude(self::REL_RANGE));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getSlotsRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $relatedSlots = $resource->slots;
        } else {
            $relatedSlots = array_map(function ($slot) {
                return \ConsultationSlot::build(['id' => $slot->id], false);
            }, $resource->slots);
        }

        $relationships[self::REL_SLOTS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_SLOTS),
            ],
            self::RELATIONSHIP_DATA => $relatedSlots,
        ];

        return $relationships;
    }

    private function getRangeRelationship($relationships, $resource, $includeData)
    {
        $range = $resource->range;

        $relationships[self::REL_RANGE] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getLinkForRange($range),
            ],
            self::RELATIONSHIP_DATA => $includeData ? $range : $this->getMinimalRange($range),
        ];

        return $relationships;
    }

    private function getLinkForRange(Range $range)
    {
        if (
            $range instanceof \Course ||
            $range instanceof \Institute ||
            $range instanceof \User
        ) {
            return $this->createLinkToResource($range);
        }

        throw new \Exception('Unknown range type');
    }

    private function getMinimalRange(Range $range)
    {
        if ($range instanceof \Course) {
            return Course::build(['id' => $range->id], false);
        }

        if ($range instanceof \Institute) {
            return Institute::build(['id' => $range->id], false);
        }

        if ($range instanceof \User) {
            return User::build(['id' => $range->id], false);
        }

        throw new \Exception('Unknown range type');
    }
}
