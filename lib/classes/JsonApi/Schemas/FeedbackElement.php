<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class FeedbackElement extends SchemaProvider
{
    const TYPE = 'feedback-elements';
    const REL_AUTHOR = 'author';
    const REL_COURSE = 'course';
    const REL_ENTRIES = 'entries';
    const REL_RANGE = 'range';



    public function getId($resource): ?string
    {
        return (int) $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = [
            'question' => (string) $resource['question'],
            'description' => (string) $resource['description'],
            'mode' => (int) $resource['mode'],
            'results-visible' => (bool) $resource['results_visible'],
            'is-commentable' => (bool) $resource['commentable'],

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate'])
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function hasResourceMeta($resource): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getResourceMeta($resource)
    {
        return $resource['mode'] === 0
            ? null
            : [
                'ratings' => [
                    'count' => \FeedbackEntry::countBySql('feedback_id = ?', [$resource->id]),
                    'mean' => (float) $resource->getMeanOfRating()
                ]
            ];
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $relationships = $this->getAuthorRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_AUTHOR));
        $relationships = $this->getCourseRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_COURSE));
        $relationships = $this->getEntriesRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_ENTRIES));
        $relationships = $this->getRangeRelationship($relationships, $resource, $this->shouldInclude($context, self::REL_RANGE));

        return $relationships;
    }

    private function getAuthorRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        $userId = $resource['user_id'];
        $related = $includeData ? \User::find($userId) : \User::build(['id' => $userId], false);
        $relationships[self::REL_AUTHOR] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($related)
            ],
            self::RELATIONSHIP_DATA => $related
        ];

        return $relationships;
    }

    private function getCourseRelationship(array $relationships, \FeedbackElement $resource, $includeData): array
    {
        if ($courseId = $resource['course_id']) {
            $related = $includeData ? \Course::find($courseId) : \Course::build(['id' => $courseId], false);
            $relationships[self::REL_COURSE] = [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($related)
                ],
                self::RELATIONSHIP_DATA => $related
            ];
        }

        return $relationships;
    }

    private function getEntriesRelationship(array $relationships, \FeedbackElement $resource, bool $includeData): array
    {
        $relationships[self::REL_ENTRIES] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_ENTRIES)
            ],
            self::RELATIONSHIP_DATA => $resource->entries
        ];

        return $relationships;
    }

    private function getRangeRelationship(array $relationships, \FeedbackElement $resource, bool $includeData): array
    {
        $rangeType = $resource['range_type'];
        $link = null;

        try {
            $link = $this->createLinkToResource($rangeType);
            if (
                is_subclass_of($rangeType, \FeedbackRange::class) &&
                is_subclass_of($rangeType, \SimpleORMap::class)
            ) {
                if ($range = $rangeType::find($resource['range_id'])) {
                    $relationships[self::REL_RANGE] = [
                        self::RELATIONSHIP_LINKS => [Link::RELATED => $link],
                        self::RELATIONSHIP_DATA => $range
                    ];
                }
            }
        } catch (\InvalidArgumentException $e) {
        }

        return $relationships;
    }
}
