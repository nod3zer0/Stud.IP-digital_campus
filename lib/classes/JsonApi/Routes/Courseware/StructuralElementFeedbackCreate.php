<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use JsonApi\Schemas\Courseware\StructuralElementFeedback as StructuralElementFeedbackSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\Activity\Activity;
use Studip\Activity\CoursewareProvider;

/**
 * Create feedback on a structural-element.
 */
class StructuralElementFeedbackCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $structuralElement = $this->getStructuralElementFromJson($json);
        if (!Authority::canCreateStructuralElementFeedback($user = $this->getUser($request), $structuralElement)) {
            throw new AuthorizationFailedException();
        }
        $structuralElementFeedback = $this->createStructuralElementFeedback($user, $json, $structuralElement);

        return $this->getCreatedResponse($structuralElementFeedback);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (StructuralElementFeedbackSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.attributes.feedback')) {
            return 'Missing `feedback` attribute.';
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

    private function createStructuralElementFeedback(\User $user, array $json, \Courseware\StructuralElement $structuralElement)
    {
        $structuralElementFeedback = \Courseware\StructuralElementFeedback::build([
            'structural_element_id' => $structuralElement->id,
            'user_id' => $user->id,
            'feedback' => self::arrayGet($json, 'data.attributes.feedback'),
        ]);
        $structuralElementFeedback->store();

        return $structuralElementFeedback;
    }
}
