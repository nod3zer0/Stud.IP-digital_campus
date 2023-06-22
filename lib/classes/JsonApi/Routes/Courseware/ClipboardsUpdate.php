<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Clipboard;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Unit as UnitSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Clipboard.
 * 
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.4
 */
class ClipboardsUpdate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = Clipboard::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canUpdateClipboard($user, $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateClipboard($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $resource)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
    }

    private function updateClipboard(\User $user, Clipboard $resource, array $json): Clipboard
    {
        if (self::arrayHas($json, 'data.attributes.name')) {
            $resource->name = self::arrayGet($json, 'data.attributes.name');
        }
        if (self::arrayHas($json, 'data.attributes.description')) {
            $resource->description = self::arrayGet($json, 'data.attributes.description');
        }

        $resource->store();

        return $resource;
    }
}
