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
        if (!Authority::canUpdateTask($user = $this->getUser($request), $resource)) {
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
        if (Authority::canDeleteTask($user, $resource)) {
            if (self::arrayHas($json, 'data.attributes.renewal')) {
                $newRenewalState = self::arrayGet($json, 'data.attributes.renewal');
                if ('declined' === $newRenewalState) {
                    $resource->renewal = $newRenewalState;
                }
                if ('granted' === $newRenewalState && self::arrayHas($json, 'data.attributes.renewal-date')) {
                    $renewalDate = self::arrayGet($json, 'data.attributes.renewal-date', '');
                    $renewalDate = self::fromISO8601($renewalDate);

                    $resource->renewal = $newRenewalState;
                    $resource->renewal_date = $renewalDate->getTimestamp();
                }
            }
        } else {
            if (self::arrayHas($json, 'data.attributes.submitted')) {
                $newSubmittedState = self::arrayGet($json, 'data.attributes.submitted');
                if ($this->canSubmit($resource, $newSubmittedState)) {
                    $resource->submitted = $newSubmittedState;
                    if ('pending' === $resource->renewal) {
                        $resource->renewal = '';
                    }
                }
            }
            if (self::arrayHas($json, 'data.attributes.renewal')) {
                $newRenewalState = self::arrayGet($json, 'data.attributes.renewal');
                if ('pending' === $newRenewalState) {
                    $resource->renewal = $newRenewalState;
                }
            }
        }

        $resource->store();

        return $resource;
    }

    private function canSubmit(Task $resource, string $newSubmittedState): bool
    {
        $now = time();
        if (1 === (int) $resource->submitted || !$newSubmittedState) {
            return false;
        }
        if ('granted' === $resource->renewal) {
            return $now <= $resource->renewal_date;
        } else {
            return $now <= $resource->submission_date;
        }
    }
}
