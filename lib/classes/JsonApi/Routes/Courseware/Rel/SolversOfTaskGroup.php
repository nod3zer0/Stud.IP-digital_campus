<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\StructuralElement;
use Courseware\Task;
use Courseware\TaskGroup;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\RelationshipsController;
use JsonApi\Schemas\Courseware\TaskGroup as TaskGroupSchema;
use JsonApi\Schemas\StatusGroup as StatusGroupSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ServerRequestInterface as Request;
use Statusgruppen;
use User;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class SolversOfTaskGroup extends RelationshipsController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $solvers = $related->getSolvers();
        $total = count($solvers);

        return $this->getPaginatedIdentifiersResponse(array_slice($solvers, ...$this->getOffsetAndLimit()), $total);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function addToRelationship(Request $request, $related)
    {
        $this->createTaskFor(
            $related,
            array_filter($this->validateSolvers($related, $this->validate($request)), function ($solver) use (
                $related
            ) {
                return !$related->findTaskBySolver($solver);
            })
        );

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        $related = TaskGroup::find($args['id']);
        if (!$related) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canShowTaskGroup($this->getUser($request), $resource);
            case 'POST':
                return Authority::canUpdateTaskGroup($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, TaskGroupSchema::REL_SOLVERS);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, TaskGroupSchema::REL_SOLVERS);
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        $data = self::arrayGet($json, 'data');

        if (!is_array($data)) {
            return 'Document´s `data` must be an array.';
        }

        foreach ($data as $item) {
            if (!in_array(self::arrayGet($item, 'type'), [UserSchema::TYPE, StatusGroupSchema::TYPE])) {
                return 'Wrong `type` in document´s `data`.';
            }

            if (!self::arrayGet($item, 'id')) {
                return 'Missing `id` of document´s `data`.';
            }
        }
    }

    private function validateSolvers(TaskGroup $taskGroup, iterable $json): iterable
    {
        if (!$taskGroup->course) {
            return [];
        }
        $solvers = [];
        foreach ($json['data'] as $item) {
            $solver = $this->findSolver($item);
            if (!$solver) {
                throw new RecordNotFoundException();
            }
            if (!$this->validateSolver($taskGroup, $solver)) {
                throw new UnprocessableEntityException();
            }
            $solvers[] = $solver;
        }
        return $solvers;
    }

    /**
     * @return Statusgruppen|User|null
     */
    private function findSolver($json)
    {
        switch ($json['type']) {
            case 'status-groups':
                return Statusgruppen::find($json['id']);
            case 'users':
                return User::find($json['id']);
        }
        return null;
    }

    /**
     * @param Statusgruppen|User $solver
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function validateSolver(TaskGroup $taskGroup, $solver): bool
    {
        if ($solver instanceof User) {
            return $GLOBALS['perm']->have_studip_perm('autor', $taskGroup->course->id, $solver->id);
        }
        if ($solver instanceof Statusgruppen) {
            return $taskGroup->course->id === $solver->range_id;
        }

        return false;
    }

    /**
     * @param array<User|Statusgruppen> $solvers
     */
    private function createTaskFor(TaskGroup $taskGroup, $solvers): void
    {
        $template = $this->getTaskTemplate($taskGroup);
        if (!$template) {
            throw new RuntimeException();
        }

        foreach ($solvers as $solver) {
            $task = Task::build([
                'task_group_id' => $taskGroup->id,
                'solver_id' => $solver->id,
                'solver_type' => $this->getSolverType($solver),
            ]);

            $taskElement = $template->copy($taskGroup->lecturer, $taskGroup->target, 'task');
            $taskElement->title = $taskGroup->title;
            $taskElement->store();

            $task['structural_element_id'] = $taskElement->id;
            $task->store();
        }
    }

    private function getTaskTemplate(TaskGroup $taskGroup): StructuralElement
    {
        return StructuralElement::find($taskGroup->task_template_id);
    }

    /**
     * @param User|Statusgruppen $solver
     */
    private function getSolverType($solver): string
    {
        $solverTypes = [\User::class => 'autor', \Statusgruppen::class => 'group'];

        return $solverTypes[get_class($solver)];
    }
}
