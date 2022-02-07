<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use JsonApi\Schemas\Courseware\StructuralElementComment as StructuralElementCommentSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\Activity\Activity;
use Studip\Activity\CoursewareProvider;
use Courseware\Container;

/**
 * Create a comment on a StructuralElement.
 */
class StructuralElementCommentsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $structuralElement = $this->getStructuralElementFromJson($json);
        if (!Authority::canCreateStructuralElementComment($user = $this->getUser($request), $structuralElement)) {
            throw new AuthorizationFailedException();
        }
        $structuralElementComment = $this->createStructuralElementComment($user, $json, $structuralElement);

        return $this->getCreatedResponse($structuralElementComment);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (StructuralElementCommentSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.comment')) {
            return 'Missing `comment` attribute.';
        }

        if (!self::arrayHas($json, 'data.relationships.structural-element')) {
            return 'Missing `structural-element` relationship.';
        }
        if (!$this->getStructuralElementFromJson($json)) {
            return 'Invalid `structural-element` relationship.';
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

    private function createStructuralElementComment(\User $user, array $json, \Courseware\StructuralElement $structuralElement)
    {
        $structuralElementComment = \Courseware\StructuralElementComment::build([
            'structural_element_id' => $structuralElement->id,
            'user_id' => $user->id,
            'comment' => self::arrayGet($json, 'data.attributes.comment', ''),
        ]);
        $structuralElementComment->store();

        return $structuralElementComment;
    }
}
