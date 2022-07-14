<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Task;
use Courseware\TaskFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Task as TaskSchema;
use JsonApi\Schemas\Courseware\TaskFeedback as TaskFeedbackSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\Activity\Activity;
use Studip\Activity\CoursewareProvider;

/**
 * Create a Task.
 */
class TaskFeedbackCreate extends JsonApiController
{
    use ValidationTrait;

        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $task = $this->getTaskFromJson($json);
        if (!Authority::canCreateTaskFeedback($lecturer = $this->getUser($request), $task)) {
            throw new AuthorizationFailedException();
        }

        $feedback = $this->createTaskFeedback($lecturer, $json, $task);

        return $this->getCreatedResponse($feedback);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (TaskFeedbackSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }
        if (!$this->getTaskFromJson($json)) {
            return 'Invalid `task` relationship.';
        }
    }

    private function getTaskFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.task', TaskSchema::TYPE)) {
            return null;
        }

        $taskId = self::arrayGet($json, 'data.relationships.task.data.id');

        return \Courseware\Task::find($taskId);
    }

    private function createTaskFeedback(\User $lecturer, array $json, \Courseware\Task $task): TaskFeedback
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $feedback = TaskFeedback::build([
            'lecturer_id' => $lecturer->id,
            'task_id' => $task->id,
            'content' => self::arrayGet($json, 'data.attributes.content', '')
        ]);

        $feedback->store();
        $task->feedback_id = $feedback->id;
        $task->store();

        return $feedback;
    }
}
