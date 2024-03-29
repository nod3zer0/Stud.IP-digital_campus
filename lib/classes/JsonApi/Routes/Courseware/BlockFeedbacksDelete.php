<?php

namespace JsonApi\Routes\Courseware;

use Courseware\BlockFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Delete one comment on a block.
 */
class BlockFeedbacksDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = BlockFeedback::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canDeleteBlockFeedback($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource->delete();

        return $this->getCodeResponse(204);
    }
}
