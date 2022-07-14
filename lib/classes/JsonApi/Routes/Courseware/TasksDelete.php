<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Task;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one Task.
 */
class TasksDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?\Courseware\Task $resource */
        $resource = Task::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteTask($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        if ($feedback = $resource->getFeedback()) {
            $feedback->delete();
        }
        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
