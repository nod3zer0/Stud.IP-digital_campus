<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElementComment;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use JsonApi\Schemas\Courseware\StructuralElementComment as StructuralElementCommentSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a comment on a structural element
 */
class StructuralElementCommentsUpdate extends JsonApiController
{
    use ValidationTrait, UserProgressesHelper;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = StructuralElementComment::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateStructuralElementComment($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $structuralElementComment = $this->updateStructuralElementComment($json, $resource);

        return $this->getContentResponse($structuralElementComment);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     * @SuppressWarnings(CyclomaticComplexity)
     * @SuppressWarnings(NPathComplexity)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (StructuralElementCommentSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Mismatch in document `id`.';
        }

        if (!($comment = self::arrayGet($json, 'data.attributes.comment'))) {
            return 'Missing `comment` attribute.';
        }
        if (!is_string($comment)) {
            return 'Attribute `comment` must be a string.';
        }
        if ($comment == '') {
            return 'Attribute `comment` must not be empty.';
        }

        if (self::arrayHas($json, 'data.relationships.user')) {
            if (!($user = $this->getUserFromJson($json))) {
                return 'Invalid `user` relationship.';
            }
            if ($user->id !== $data['user_id']) {
                return 'Cannot update `user` relationship.';
            }
        }

        if (self::arrayHas($json, 'data.relationships.structural-element')) {
            if (!($structural_element = $this->getStructuralElementFromJson($json))) {
                return 'Invalid `structural-element` relationship.';
            }
            if ($structural_element->id !== $data['structural_element_id']) {
                return 'Cannot update `structural-element` relationship.';
            }
        }
    }

    private function getStructuralElementFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.structural-element', StructuralElementSchema::TYPE)) {
            return null;
        }
        $structuralElementId = self::arrayGet($json, 'data.relationships.structural-element.data.id');

        return \Courseware\StructuralElement::find($structuralElementId);
    }

    private function getUserFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.user', UserSchema::TYPE)) {
            return null;
        }
        $userId = self::arrayGet($json, 'data.relationships.user.data.id');

        return \User::find($userId);
    }

    private function updateStructuralElementComment(array $json, \Courseware\StructuralElementComment $resource)
    {
        $resource->comment = self::arrayGet($json, 'data.attributes.comment', '');
        $resource->store();

        return $resource;
    }
}
