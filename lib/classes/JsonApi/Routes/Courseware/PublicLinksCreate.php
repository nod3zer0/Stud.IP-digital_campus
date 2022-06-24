<?php

namespace JsonApi\Routes\Courseware;

use Courseware\PublicLink;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a Template.
 */
class PublicLinksCreate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        if (!Authority::canCreatePublicLink($user = $this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $publicLink = $this->createPublicLink($json, $user);

        return $this->getCreatedResponse($publicLink);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (!self::arrayHas($json, 'data.relationships.structural-element.data.id')) {
            return 'Missing `structural-element-id` value.';
        }

    }

    private function createPublicLink(array $json, $user): PublicLink
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $publicLink = new PublicLink();

        $publicLink->setId($publicLink->getNewId());
        $publicLink->user_id = $user->id;

        $publicLink->structural_element_id = $get('data.relationships.structural-element.data.id');
        $publicLink->password = str_replace(' ', '', $get('data.attributes.password'));
        $expire_date = $get('data.attributes.expire-date');
        $expireDate = self::fromISO8601($expire_date);
        $publicLink->expire_date =  $expireDate->getTimestamp();

        $publicLink->store();

        return $publicLink;
    }
}
