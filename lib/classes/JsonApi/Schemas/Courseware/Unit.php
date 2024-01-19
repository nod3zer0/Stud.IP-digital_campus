<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Unit extends SchemaProvider
{
    const TYPE = 'courseware-units';

    const REL_CREATOR= 'creator';
    const REL_RANGE = 'range';
    const REL_STRUCTURAL_ELEMENT = 'structural-element';
    const REL_FEEDBACK_ELEMENT = 'feedback-element';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        return $resource->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'content-type' => (string) $resource['content_type'],
            'position' => (int) $resource['position'],
            'public' => (int) $resource['public'],
            'release-date' => $resource['release_date'] ? date('c', $resource['release_date']) : null,
            'withdraw-date' => $resource['withdraw_date'] ? date('c', $resource['withdraw_date']) : null,
            'config' => json_decode($resource['config']),
            'mkdate'    => date('c', $resource['mkdate']),
            'chdate'    => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $relationships[self::REL_CREATOR] = $resource->creator
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->creator),
                ],
                self::RELATIONSHIP_DATA => $resource->creator,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_STRUCTURAL_ELEMENT] = $resource->structural_element
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->structural_element),
                ],
                self::RELATIONSHIP_DATA => $resource->structural_element,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $rangeType = $resource->range_type;
        $range = $resource->$rangeType;

        $relationships[self::REL_RANGE] = $range
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($range),
                ],
                self::RELATIONSHIP_DATA => $range,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $feedback = $resource->getFeedbackElement();
        $relationships[self::REL_FEEDBACK_ELEMENT] = $feedback
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($feedback),
                ],
                self::RELATIONSHIP_DATA => $feedback,
            ]
            : [self::RELATIONSHIP_DATA => null];

        return $relationships;
    }
}
