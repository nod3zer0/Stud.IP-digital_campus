<?php

namespace JsonApi\Routes\Lti;

use JsonApi\Errors\AuthorizationFailedException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\JsonApiController;

class LtiToolsIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        if (!Authority::canIndexLtiTools($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();

        $total = \LtiTool::countBySql('1');
        $tools = \LtiTool::findBySQL("1 ORDER BY `name` LIMIT ?, ?", [$offset, $limit]);

        return $this->getPaginatedContentResponse($tools, $total);
    }
}
