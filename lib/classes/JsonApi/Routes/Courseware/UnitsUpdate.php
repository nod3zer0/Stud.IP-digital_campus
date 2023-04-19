<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Unit;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Unit as UnitSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Block.
 */
class UnitsUpdate extends JsonApiController
{
    use EditBlockAwareTrait;
    use TimestampTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = Unit::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canUpdateUnit($user, $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateUnit($user, $resource, $json);

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

        if (UnitSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.attributes.release-date')) {
            $releaseDate = self::arrayGet($json, 'data.attributes.release-date');
            if (!self::isValidTimestamp($releaseDate)) {
                return '`release-date` is not an ISO 8601 timestamp.';
            }
        }

        if (self::arrayHas($json, 'data.attributes.withdraw-date')) {
            $withdrawDate = self::arrayGet($json, 'data.attributes.withdraw-date');
            if (!self::isValidTimestamp($withdrawDate)) {
                return '`withdraw-date` is not an ISO 8601 timestamp.';
            }
        }
    }

    private function updateUnit(\User $user, Unit $resource, array $json): Unit
    {
        if (self::arrayHas($json, 'data.attributes.public')) {
            $resource->public = self::arrayGet($json, 'data.attributes.public');
        }

        if (self::arrayHas($json, 'data.attributes.release-date')) {
            $releaseDate = self::arrayGet($json, 'data.attributes.release-date', '');
            $releaseDate = self::fromISO8601($releaseDate);
            $resource->release_date = $releaseDate->getTimestamp();
        }

        if (self::arrayHas($json, 'data.attributes.withdraw-date')) {
            $withdrawDate = self::arrayGet($json, 'data.attributes.withdraw-date', '');
            $withdrawDate = self::fromISO8601($withdrawDate);
            $resource->withdraw_date = $withdrawDate->getTimestamp();
        }

        $resource->store();

        return $resource;
    }
}
