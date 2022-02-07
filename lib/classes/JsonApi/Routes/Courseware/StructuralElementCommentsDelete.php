<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElementComment;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one comment on a structural element.
 */
class StructuralElementCommentsDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = StructuralElementComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteStructuralElementComment($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
