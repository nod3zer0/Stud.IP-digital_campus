<?php

namespace JsonApi\Routes\Courseware;

use Courseware\PublicLink;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one PublicLink.
 */
class PublicLinksDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = PublicLink::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeletePublicLink($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
