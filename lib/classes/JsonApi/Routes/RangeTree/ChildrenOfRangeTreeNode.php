<?php

namespace JsonApi\Routes\RangeTree;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class ChildrenOfRangeTreeNode extends JsonApiController
{
    protected $allowedIncludePaths = [
        'children',
        'courses',
        'institute',
        'parent',
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!RangeTreeNode::getNode($args['id'])) {
            throw new RecordNotFoundException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = \RangeTreeNode::countByParent_id($args['id']);
        $children = \RangeTreeNode::findByParent_id(
            $args['id'],
            "LIMIT {$offset}, {$limit}"
        );

        return $this->getPaginatedContentResponse($children, $total);
    }
}
