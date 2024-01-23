<?php

namespace JsonApi\Routes\Courseware;

use Courseware\TaskGroup;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\TaskGroup as TaskGroupSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use User;

/**
 * Updates one TaskGroup.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class TaskGroupsUpdate extends JsonApiController
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
        /** @var ?\Courseware\TaskGroup $resource */
        $resource = TaskGroup::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canUpdateTaskGroup($user, $resource)) {
            throw new AuthorizationFailedException();
        }

        $process = $this->update($resource, $json);

        return $this->getContentResponse($process);
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
            return 'Invalid `type` of document´s `data`.';
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

        if (self::fromISO8601($startDate) > self::fromISO8601($endDate)) {
            return '`start-date` is later than `end-date`';
        }
    }

    private function update(TaskGroup $taskGroup, array $json): TaskGroup
    {
        $startDate = self::fromISO8601(self::arrayGet($json, 'data.attributes.start-date'));
        $endDate = self::fromISO8601(self::arrayGet($json, 'data.attributes.end-date'));

        $taskGroup->start_date = $startDate->getTimestamp();
        $taskGroup->end_date = $endDate->getTimestamp();

        $taskGroup->store();

        return $taskGroup;
    }
}
