<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Task;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Task.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class TasksUpdate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;

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
        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canUpdateTask($user, $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateTask($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     * @param array $json
     * @param mixed $data
     * @return string|void
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.attributes.renewal-date')) {
            $renewalDate = self::arrayGet($json, 'data.attributes.renewal-date');
            if (!self::isValidTimestamp($renewalDate)) {
                return '`renewal-date` is not an ISO 8601 timestamp.';
            }
        }
    }

    private function updateTask(\User $user, Task $resource, array $json): Task
    {
        if (Authority::canRenewTask($user, $resource)) {
            return $this->renewTask($resource, $json);
        }

        if (self::arrayGet($json, 'data.attributes.submitted') === true && $resource->canSubmit()) {
            $resource->submitTask();
        }

        if (self::arrayGet($json, 'data.attributes.renewal') === 'pending') {
            $resource->requestRenewal();
        }

        return $resource;
    }

    private function renewTask(Task $resource, array $json): Task
    {
        switch (self::arrayGet($json, 'data.attributes.renewal')) {
            case 'declined':
                $resource->declineRenewalRequest();
                break;

            case 'granted':
                $resource->grantRenewalRequest(
                    self::fromISO8601(self::arrayGet($json, 'data.attributes.renewal-date'))
                );
                break;
        }

        return $resource;
    }
}
