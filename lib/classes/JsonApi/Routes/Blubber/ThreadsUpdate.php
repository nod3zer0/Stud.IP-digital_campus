<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\TimestampTrait;
use JsonApi\Routes\ValidationTrait;

/**
 * Update a blubber thread.
 */
class ThreadsUpdate extends JsonApiController
{
    use TimestampTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        $thread = \BlubberThread::find($args['id']);
        if (!$thread) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);
        if (!Authority::canEditBlubberThread($user, $thread)) {
            throw new AuthorizationFailedException();
        }

        $visitedAt = self::arrayGet($json, 'data.attributes.visited-at');
        if ($visitedAt) {
            $visitedDate = self::fromISO8601($visitedAt)->getTimestamp();
            $GLOBALS['user']->cfg->store('BLUBBERTHREAD_VISITED_' . $thread->getId(), $visitedDate);
        }

        return $this->getContentResponse($thread);
    }

    protected function validateResourceDocument($json)
    {
        if (self::arrayHas($json, 'data.attributes.visited-at')) {
            $visitedAt = self::arrayGet($json, 'data.attributes.visited-at');
            if (!self::isValidTimestamp($visitedAt)) {
                return '`visited-at` is not an ISO 8601 timestamp.';
            }
        }
    }
}
