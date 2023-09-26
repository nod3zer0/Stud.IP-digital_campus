<?php

namespace JsonApi\Routes\Blubber;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\BlubberThread as ThreadSchema;
use JsonApi\Routes\ValidationTrait;

/**
 * Create a new blubber comment.
 */
class CommentsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (isset($args['id'])) {
            $json = $this->validate($request, $args['id']);
            $thread = \BlubberThread::find($args['id']);
        } else {
            $json = $this->validate($request, null);
            $thread = $this->getThreadFromJson($json);
        }

        if (!$thread) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canCreateComment($user = $this->getUser($request), $thread)) {
            throw new AuthorizationFailedException();
        }

        $content = self::arrayGet($json, 'data.attributes.content');

        $comment = \BlubberComment::create([
            'thread_id' => $thread->id,
            'content' => $content,
            'user_id' => $user->id,
            'external_contact' => 0,
        ]);

        return $this->getCreatedResponse($comment);
    }

    protected function validateResourceDocument($json, $id = null)
    {
        if (!self::arrayHas($json, 'data.attributes.content')) {
            return 'No comment provided';
        }

        if (mb_strlen(trim(self::arrayGet($json, 'data.attributes.content'))) === 0) {
            return 'Comment should not be empty.';
        }

        if (!$id && !$this->getThreadFromJson($json)) {
            return 'Invalid `block` relationship.';
        }
    }

    private function getThreadFromJson($json)
    {
        $relationship = 'thread';
        if (!$this->validateResourceObject($json, 'data.relationships.' . $relationship, ThreadSchema::TYPE)) {
            return null;
        }
        $resourceId = self::arrayGet($json, 'data.relationships.' . $relationship . '.data.id');

        return \BlubberThread::find($resourceId);
    }
}
