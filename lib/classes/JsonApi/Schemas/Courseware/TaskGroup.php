<?php

namespace JsonApi\Schemas\Courseware;

use Courseware\StructuralElement;
use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Identifier;
use Neomerx\JsonApi\Schema\Link;

class TaskGroup extends SchemaProvider
{
    const TYPE = 'courseware-task-groups';

    const REL_COURSE = 'course';
    const REL_LECTURER = 'lecturer';
    const REL_SOLVERS = 'solvers';
    const REL_TARGET = 'target';
    const REL_TASK_TEMPLATE = 'task-template';
    const REL_TASKS = 'tasks';

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
            'solver-may-add-blocks' => (bool) $resource['solver_may_add_blocks'],
            'title' => (string) $resource->title,
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

        $relationships[self::REL_COURSE] = $resource['seminar_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->course),
                ],
                self::RELATIONSHIP_DATA => $resource->course,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_LECTURER] = $resource['lecturer_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->lecturer),
                ],
                self::RELATIONSHIP_DATA => $resource->lecturer,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_SOLVERS] = [
            self::RELATIONSHIP_DATA => $resource->getSolvers(),
        ];

        $target = StructuralElement::build(['id' => $resource['target_id']]);
        $relationships[self::REL_TARGET] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($target),
            ],
            self::RELATIONSHIP_DATA => $this->shouldInclude($context, self::REL_TARGET) ? $resource['target'] : $target,
        ];

        $taskTemplate = StructuralElement::build(['id' => $resource['task_template_id']]);
        $relationships[self::REL_TASK_TEMPLATE] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($taskTemplate),
            ],
            self::RELATIONSHIP_DATA => $this->shouldInclude($context, self::REL_TASK_TEMPLATE)
                ? $resource['task_template']
                : $taskTemplate,
        ];

        $relationships[self::REL_TASKS] = [
            self::RELATIONSHIP_DATA => $this->shouldInclude($context, self::REL_TASKS)
                ? $resource['tasks']
                : \DBManager::get()->fetchFirst(
                    'SELECT id FROM cw_tasks WHERE task_group_id = ?',
                    [$resource->getId()],
                    function ($id) {
                        return new Identifier($id, Task::TYPE);
                    }
                ),
        ];

        return $relationships;
    }
}
