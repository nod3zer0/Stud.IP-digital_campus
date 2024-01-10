<?php

namespace JsonApi\Routes\Feedback;

use FeedbackElement;
use FeedbackRange;
use User;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\FeedbackElement as FeedbackElementSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a FeedbackElement.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FeedbackElementsUpdate extends JsonApiController
{
    use RangeTypeAware;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $this->preparePossibleRangeTypes();
        $resource = \FeedbackElement::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        $json = $this->validate($request);
        $user = $this->getUser($request);

        if (!Authority::canUpdateFeedbackElement($user, $resource)) {
            throw new AuthorizationFailedException();
        }

        $feedbackElement = $this->update($resource, $json);

        return $this->getContentResponse($feedbackElement);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     *
     * @param array $json
     * @param mixed $data
     *
     * @return string|void
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (FeedbackElementSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }
        if (!self::arrayHas($json, 'data.id')) {
            return 'An existing document must have an `id`.';
        }

        $required = ['question', 'description'];
        foreach ($required as $attribute) {
            if (!self::arrayHas($json, 'data.attributes.' . $attribute)) {
                return 'Missing `' . $attribute . '` attribute.';
            }
        }
    }

    private function update(FeedbackElement $feedbackElement, array $json): FeedbackElement
    {
        $strAttrs = ['question', 'description'];
        foreach ($strAttrs as $attribute) {
            if (self::arrayHas($json, 'data.attributes.' . $attribute)) {
                $feedbackElement[$attribute] = self::arrayGet($json, 'data.attributes.' . $attribute);
            }
        }

        $feedbackElement->store();

        return $feedbackElement;
    }
}
