<?php

namespace JsonApi\Routes\Courseware;

use Courseware\PublicLink;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one PublicLink.
 */
class PublicLinksUpdate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;
        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = PublicLink::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdatePublicLink($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updatePublicLink($resource, $json);

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

        if (self::arrayHas($json, 'data.attributes.expire-date')) {
            $expire_date = self::arrayGet($json, 'data.attributes.expire-date');
            if (!self::isValidTimestamp($expire_date)) {
                return '`expire-date` is not an ISO 8601 timestamp.';
            }
        }
    }

    private function updatePublicLink(PublicLink $resource, array $json): PublicLink
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $resource->password = $get('data.attributes.password');

        $expire_date = $get('data.attributes.expire-date');
        $expireDate = self::fromISO8601($expire_date);
        $resource->expire_date =  $expireDate->getTimestamp();

        $resource->store();

        return $resource;
    }

}
