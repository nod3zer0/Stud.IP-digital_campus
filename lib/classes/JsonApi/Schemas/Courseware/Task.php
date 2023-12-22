<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Task extends SchemaProvider
{
    const TYPE = 'courseware-tasks';

    const REL_FEEDBACK = 'task-feedback';
    const REL_SOLVER = 'solver';
    const REL_STRUCTURAL_ELEMENT = 'structural-element';
    const REL_TASK_GROUP = 'task-group';

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
            'progress' => (float) $resource->getTaskProgress(),
            'submission-date' => date('c', $resource['submission_date']),
            'submitted' => (bool) $resource['submitted'],
            'renewal' => empty($resource['renewal']) ? null : (string) $resource['renewal'],
            'renewal-date' => date('c', $resource['renewal_date']),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $feedback = $resource->getFeedback();
        $relationships[self::REL_FEEDBACK] = $feedback
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($feedback),
                ],
                self::RELATIONSHIP_DATA => $feedback,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $solver = $resource->getSolver();
        $relationships[self::REL_SOLVER] = $solver
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($solver),
                ],
                self::RELATIONSHIP_DATA => $solver,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_STRUCTURAL_ELEMENT] = $resource['structural_element_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource['structural_element']),
                ],
                self::RELATIONSHIP_DATA => $resource['structural_element'],
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_TASK_GROUP] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($resource['task_group']),
            ],
            self::RELATIONSHIP_DATA => $resource['task_group'],
        ];

        return $relationships;
    }
}
