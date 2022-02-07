<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Template;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Template.
 */
class TemplatesUpdate extends JsonApiController
{
    use ValidationTrait;
        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = Template::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateTemplate($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateTemplate($resource, $json);

        return $this->getContentResponse($resource);
    }

        /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (!self::arrayHas($json, 'data.name')) {
            return 'Document must have an `name`.';
        }

        if (!self::arrayHas($json, 'data.purpose')) {
            return 'Document must have an `purpose`.';
        }
    }

    private function updateTemplate(Template $resource, array $json): Template
    {
        if (self::arrayHas($json, 'data.name')) {
            $resource->name = self::arrayGet(
                $json,
                'data.name'
            );
        }

        if (self::arrayHas($json, 'data.purpose')) {
            $resource->purpose = self::arrayGet(
                $json,
                'data.purpose'
            );
        }

        $resource->store();

        return $resource;
    }

}