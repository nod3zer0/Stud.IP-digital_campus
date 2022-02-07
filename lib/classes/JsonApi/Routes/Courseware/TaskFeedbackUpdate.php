<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Task;
use Courseware\TaskFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\Activity\Activity;
use Studip\Activity\CoursewareProvider;

/**
 * Update one Task.
 */
class TaskFeedbackUpdate extends JsonApiController
{
    use ValidationTrait;
        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = TaskFeedback::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        $task = Task::find($resource->task_id);
        if (!Authority::canUpdateTaskFeedback($user = $this->getUser($request), $task)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateTaskFeedback($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }
    }

    private function updateTaskFeedback(\User $user, TaskFeedback $resource, array $json): TaskFeedback
    {
        if (self::arrayHas($json, 'data.attributes.content')) {
            $resource->content = self::arrayGet(
                $json,
                'data.attributes.content'
            );
        }
        $resource->store();

        if ($struct->range_type === 'courses') {
            $data = [
                'provider'     => 'Studip\Activity\CoursewareProvider',
                'context'      => 'course',
                'context_id'   => $task->seminar_id,
                'content'      => self::arrayGet($json, 'data.attributes.content', ''),
                'actor_type'   => 'user',
                'actor_id'     => $user->id,
                'verb'         => 'answered',
                'object_id'    => $task->structural_element_id,
                'object_type'  => 'courseware',
                'mkdate'       => time()
            ];
    
            $activity = Activity::create($data);
        }

        return $resource;
    }
}