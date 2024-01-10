<?php

namespace JsonApi\Routes\Feedback;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\FeedbackElement as FeedbackElementSchema;

/**
 * Displays a certain feedback element.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FeedbackElementsShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        FeedbackElementSchema::REL_AUTHOR,
        FeedbackElementSchema::REL_COURSE,
        FeedbackElementSchema::REL_ENTRIES,
        FeedbackElementSchema::REL_RANGE,
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array $args
     *
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $resource = \FeedbackElement::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowFeedbackElement($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($resource);
    }
}
