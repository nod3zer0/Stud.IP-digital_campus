<?php

namespace JsonApi\Routes\Tree;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;

class ChildrenOfTreeNode extends JsonApiController
{
    protected $allowedFilteringParameters = ['visible'];

    protected $allowedIncludePaths = [
        'children',
        'courses',
        'institute',
        'parent',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        list($classname, $id) = explode('_', $args['id']);

        $node = $classname::getNode($id);
        if (!$node) {
            throw new RecordNotFoundException();
        }

        $filters = $this->getContextFilters();

        $data = $node->getChildNodes((bool) $filters['visible']);

        return $this->getContentResponse($data);
    }

    private function getContextFilters()
    {
        $defaults = [
            'visible' => false
        ];

        $filtering = $this->getQueryParameters()->getFilteringParameters() ?: [];

        return array_merge($defaults, $filtering);
    }
}
