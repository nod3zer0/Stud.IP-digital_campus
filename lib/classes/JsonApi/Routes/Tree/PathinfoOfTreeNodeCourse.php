<?php

namespace JsonApi\Routes\Tree;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;

class PathinfoOfTreeNodeCourse extends NonJsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $course = \Course::find($args['id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        $classname = $args['classname'];

        $path = [];
        foreach ($classname::getCourseNodes($args['id']) as $node) {
            $path[] = $node->getAncestors();
        }

        $response->getBody()->write(json_encode($path));

        return $response->withHeader('Content-type', 'application/json');
    }
}
