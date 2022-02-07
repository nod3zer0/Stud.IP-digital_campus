<?php

namespace JsonApi\Routes\Courseware;

use Courseware\TaskGroup;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\Courseware\TaskGroup as TaskGroupSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays one TaskGroup.
 */
class TaskGroupsShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        TaskGroupSchema::REL_COURSE,
        TaskGroupSchema::REL_LECTURER,
        TaskGroupSchema::REL_SOLVERS,
        TaskGroupSchema::REL_TARGET,
        TaskGroupSchema::REL_TASK_TEMPLATE,
        TaskGroupSchema::REL_TASKS,
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param array $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        /** @var ?\Courseware\TaskGroup $resource */
        $resource = TaskGroup::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowTaskGroup($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
