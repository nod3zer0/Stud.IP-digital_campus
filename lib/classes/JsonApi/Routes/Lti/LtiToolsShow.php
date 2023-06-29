<?php

namespace JsonApi\Routes\Lti;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

/**
 * Displays a certain lti tool.
 */
class LtiToolsShow extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!$resource = \LtiTool::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowLtiTool($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }


        return $this->getContentResponse($resource);
    }
}
