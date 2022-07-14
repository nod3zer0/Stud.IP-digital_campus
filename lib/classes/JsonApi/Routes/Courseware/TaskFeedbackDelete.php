<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Task;
use Courseware\TaskFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one Task.
 */
class TaskFeedbackDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = TaskFeedback::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $task = Task::find($resource->task_id);
        if (!Authority::canDeleteTaskFeedback($user = $this->getUser($request), $task)) {
            throw new AuthorizationFailedException();
        }
        $task->feedback_id = null;
        $task->store();
        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
