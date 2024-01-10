<?php

namespace JsonApi\Routes\Feedback;

use FeedbackElement;
use FeedbackEntry;
use InvalidArgumentException;
use User;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\FeedbackElement as FeedbackElementSchema;
use JsonApi\Schemas\FeedbackEntry as FeedbackEntrySchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a FeedbackEntry.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FeedbackEntriesCreate extends JsonApiController
{
    use RatingHelper;
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
        $json = $this->validate($request);
        $element = $this->getElementFromJson($json);
        $user = $this->getUser($request);

        if (!Authority::canCreateFeedbackEntry($user, $element)) {
            throw new AuthorizationFailedException();
        }

        $feedbackEntry = $this->create($user, $json);

        return $this->getCreatedResponse($feedbackEntry);
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
        if (FeedbackEntrySchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Invalid `type` of document´s `data`.';
        }
        if (self::arrayHas($json, 'data.id')) {
            return 'New document must not have an `id`.';
        }

        if (!self::arrayHas($json, 'data.relationships.feedback-element')) {
            return 'Missing `feedback-element` relationship.';
        }
        if (!$this->getElementFromJson($json)) {
            return 'Invalid `feedback-element` relationship.';
        }

        $required = ['rating'];
        foreach ($required as $attribute) {
            if (!self::arrayHas($json, 'data.attributes.' . $attribute)) {
                return 'Missing `' . $attribute . '` attribute.';
            }
        }
    }

    private function getElementFromJson(array $json): ?FeedbackElement
    {
        $relationship = FeedbackEntrySchema::REL_FEEDBACK;
        if (!$this->validateResourceObject($json, 'data.relationships.' . $relationship, FeedbackElementSchema::TYPE)) {
            return null;
        }
        $resourceId = self::arrayGet($json, 'data.relationships.' . $relationship . '.data.id');

        return FeedbackElement::find($resourceId);
    }

    private function create(User $user, array $json): FeedbackEntry
    {
        $element = $this->getElementFromJson($json);
        $entry = \FeedbackEntry::build([
            'feedback_id' => $element->getId(),
            'user_id' => $user->id,
            'rating' => $this->getRating($element, (int) self::arrayGet($json, 'data.attributes.rating')),
        ]);

        if ($element['commentable']) {
            $entry['comment'] = self::arrayGet($json, 'data.attributes.comment', '');
        }
        if ($element['anonymous_entries']) {
            $entry['anonymous'] = (int) self::arrayGet($json, 'data.attributes.anonymous', '0');
        }

        $entry->store();

        return $entry;
    }
}
