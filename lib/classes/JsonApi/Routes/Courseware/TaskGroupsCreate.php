<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use Courseware\Task;
use Courseware\TaskGroup;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use JsonApi\Schemas\Courseware\TaskGroup as TaskGroupSchema;
use JsonApi\Schemas\StatusGroup as StatusGroupSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a TaskGroup.
 */
class TaskGroupsCreate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $structuralElement = $this->getTargetFromJson($json);
        if (!Authority::canCreateTasks($user = $this->getUser($request), $structuralElement)) {
            throw new AuthorizationFailedException();
        }
        $taskGroup = $this->createTaskGroup($user, $json);

        return $this->getCreatedResponse($taskGroup);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     *
     * @param array $json
     * @param mixed $data
     *
     * @return string|void
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (TaskGroupSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }
        if (!self::arrayHas($json, 'data.attributes.title')) {
            return 'Missing `title` attribute.';
        }
        if (!self::arrayHas($json, 'data.attributes.start-date')) {
            return 'Missing `start-date` attribute.';
        }
        $startDate = self::arrayGet($json, 'data.attributes.start-date');
        if (!self::isValidTimestamp($startDate)) {
            return '`start-date` is not an ISO 8601 timestamp.';
        }
        if (!self::arrayHas($json, 'data.attributes.end-date')) {
            return 'Missing `end-date` attribute.';
        }
        $endDate = self::arrayGet($json, 'data.attributes.end-date');
        if (!self::isValidTimestamp($endDate)) {
            return '`end-date` is not an ISO 8601 timestamp.';
        }
        if (!self::arrayHas($json, 'data.relationships.target')) {
            return 'Missing `target` relationship.';
        }
        if (!self::arrayHas($json, 'data.relationships.task-template')) {
            return 'Missing `task-template` relationship.';
        }

        if (!self::arrayHas($json, 'data.relationships.solvers')) {
            return 'Missing `solvers` relationship.';
        }

        if (!$this->validateSolvers($json)) {
            return 'Invalid `solvers` relationship.';
        }
        if (!$this->getTargetFromJson($json)) {
            return 'Invalid `target` relationship.';
        }
        if (!$this->getTaskTemplateFromJson($json)) {
            return 'Invalid `task-template` relationship.';
        }
    }

    private function validateSolvers(array $json): bool
    {
        if (!self::arrayHas($json, 'data.relationships.solvers.data')) {
            return false;
        }

        $data = self::arrayGet($json, 'data.relationships.solvers.data');

        if (!is_array($data) || !count($data)) {
            return false;
        }

        foreach ($data as $resourceIdentifier) {
            if (
                !(
                    $this->validateResourceObject($resourceIdentifier, '', UserSchema::TYPE) ||
                    $this->validateResourceObject($resourceIdentifier, '', StatusGroupSchema::TYPE)
                )
            ) {
                return false;
            }
        }

        return true;
    }

    private function getSolversFromJson(array $json): iterable
    {
        if (!self::arrayHas($json, 'data.relationships.solvers.data')) {
            return [];
        }

        $solvers = [];
        $mapping = [UserSchema::TYPE => \User::class, StatusGroupSchema::TYPE => \Statusgruppen::class];
        foreach (self::arrayGet($json, 'data.relationships.solvers.data') as $resourceIdentifier) {
            $solvers[] = $mapping[$resourceIdentifier['type']]::find($resourceIdentifier['id']);
        }

        return $solvers;
    }

    private function getTargetFromJson(array $json): ?StructuralElement
    {
        if (!$this->validateResourceObject($json, 'data.relationships.target', StructuralElementSchema::TYPE)) {
            return null;
        }
        $resourceId = self::arrayGet($json, 'data.relationships.target.data.id');

        return StructuralElement::find($resourceId);
    }

    private function getTaskTemplateFromJson(array $json): ?StructuralElement
    {
        if (!$this->validateResourceObject($json, 'data.relationships.task-template', StructuralElementSchema::TYPE)) {
            return null;
        }
        $resourceId = self::arrayGet($json, 'data.relationships.task-template.data.id');

        return StructuralElement::find($resourceId);
    }

    private function createTaskGroup(\User $lecturer, array $json): TaskGroup
    {
        $tasks = [];

        $solvers = $this->getSolversFromJson($json);
        $taskTemplate = $this->getTaskTemplateFromJson($json);
        $target = $this->getTargetFromJson($json);

        $solverMayAddBlocks = self::arrayGet($json, 'data.attributes.solver-may-add-blocks', '');
        $startDate = self::fromISO8601(self::arrayGet($json, 'data.attributes.start-date', ''));
        $endDate = self::fromISO8601(self::arrayGet($json, 'data.attributes.end-date', ''));
        $title = self::arrayGet($json, 'data.attributes.title', '');

        /** @var TaskGroup $taskGroup */
        $taskGroup = TaskGroup::create([
            'seminar_id' => $target['range_id'],
            'lecturer_id' => $lecturer->getId(),
            'target_id' => $target->getId(),
            'task_template_id' => $taskTemplate->getId(),
            'solver_may_add_blocks' => $solverMayAddBlocks,
            'title' => $title,
            'start_date' => $startDate->getTimestamp(),
            'end_date' => $endDate->getTimestamp(),
        ]);

        foreach ($solvers as $solver) {
            $task = Task::build([
                'task_group_id' => $taskGroup->getId(),
                'solver_id' => $solver->getId(),
                'solver_type' => $this->getSolverType($solver),
            ]);

            // copy task template
            $purpose = 'task';
            $taskElement = $taskTemplate->copy($lecturer, $target, $purpose);
            $taskElement->title = $title;
            $taskElement->store();

            //update task with element id
            $task['structural_element_id'] = $taskElement->id;
            $task->store();
        }

        return $taskGroup;
    }

    /**
     * @param \User|\Statusgruppen $solver
     */
    private function getSolverType($solver): string
    {
        $solverTypes = [\User::class => 'autor', \Statusgruppen::class => 'group'];

        return $solverTypes[get_class($solver)];
    }
}
