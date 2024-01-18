<?php

namespace JsonApi\Routes\Tree;

use JsonApi\Errors\BadRequestException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;

class CourseinfoOfTreeNode extends NonJsonApiController
{
    protected $allowedFilteringParameters = ['q', 'semester', 'semclass', 'recursive'];

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

        $error = $this->validateFilters($request);
        if ($error) {
            throw new BadRequestException($error);
        }

        $filters = $this->getContextFilters($request);

        $info = [
            'courses' => (int) $node->countCourses($filters['semester'], $filters['semclass'], true)
        ];

        $response->getBody()->write(json_encode($info));

        return $response->withHeader('Content-type', 'application/json');
    }

    private function validateFilters($request)
    {
        $filtering = $request->getQueryParams()['filter'] ?: [];

        // keyword aka q
        if (isset($filtering['q']) && mb_strlen($filtering['q']) < 3) {
            return 'Search term too short.';
        }

        // semester
        if (isset($filtering['semester']) && $filtering['semester'] !== 'all') {
            $semester = \Semester::find($filtering['semester']);
            if (!$semester) {
                return 'Invalid "semester".';
            }
        }

        // course category
        if (!empty($filtering['semclass'])) {
            $semclass = \SeminarCategories::Get($filtering['semclass']);
            if (!$semclass) {
                return 'Invalid "course category".';
            }
        }
    }

    private function getContextFilters($request)
    {
        $defaults = [
            'q' => '',
            'semester' => 'all',
            'semclass' => 0,
            'recursive' => false
        ];

        $filtering = $request->getQueryParams()['filter'] ?: [];

        return array_merge($defaults, $filtering);
    }
}
