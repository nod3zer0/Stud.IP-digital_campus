<?php

namespace JsonApi\Routes\RangeTree;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\Institute as InstituteSchema;

class InstituteOfRangeTreeNode extends JsonApiController
{
    protected $allowedIncludePaths = [
        InstituteSchema::REL_STATUS_GROUPS,
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $node = \RangeTreeNode::find($args['id']);
        if (!$node) {
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($node->institute);
    }
}
