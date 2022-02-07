<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Template;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Task as TaskSchema;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a Template.
 */
class TemplatesCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        if (!Authority::canCreateTemplate($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        $template = $this->createTemplate($json);

        return $this->getCreatedResponse($template);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }
        if (!self::arrayHas($json, 'data.name')) {
            return 'Missing `name` value.';
        }
        if (!self::arrayHas($json, 'data.purpose')) {
            return 'Missing `purpose` attribute.';
        }
        if (!self::arrayHas($json, 'data.structure')) {
            return 'Missing `structure` attribute.';
        }
    }

    private function createTemplate(array $json): Template
    {
        $get = function ($key, $default = '') use ($json) {
            return self::arrayGet($json, $key, $default);
        };

        $template = Template::build([
            'name' => $get('data.name'),
            'purpose' => $get('data.purpose'),
            'structure' => $this->cleanStructure($get('data.structure'), $get('data.name')),
        ]);
        $template->store();

        return $template;
    }

    private function cleanStructure($json, $name): string
    {
        $structural_element_uploaded = json_decode($json, true);

        $structural_element = [
            'type' => $structural_element_uploaded['type'],
            'attributes' => ['title' => $name],
            'containers' => $structural_element_uploaded['containers'],
        ];

        return json_encode($structural_element);
    }
}
