<?php

namespace JsonApi\Routes\Blubber\Rel;

use Psr\Http\Message\ServerRequestInterface as Request;
use JsonApi\Routes\Blubber\Authority as BlubberAuthority;
use JsonApi\Routes\Users\Authority as UsersAuthority;
use JsonApi\Routes\RelationshipsController;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Schemas\User as UserSchema;

class DefaultThread extends RelationshipsController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \User $related
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $threadId = $related->getConfiguration()->getValue('BLUBBER_DEFAULT_THREAD');
        $thread = \BlubberThread::find($threadId);

        return $this->getIdentifiersResponse($thread);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $thread = isset($json['data']) ? $this->validateBlubberThread($related, $json) : null;
        $this->replaceBlubberDefaultThread($related, $thread);

        return $this->getCodeResponse(204);
    }

    private function replaceBlubberDefaultThread(\User $related, $threadOrNull)
    {
        $related->getConfiguration()->store('BLUBBER_DEFAULT_THREAD', $threadOrNull ? $threadOrNull->id : null);
    }

    protected function findRelated(array $args)
    {
        $user = \User::find($args['id']);
        if (!$user) {
            throw new RecordNotFoundException();
        }

        return $user;
    }

    /**
     * @param \User $resource
     */
    protected function authorize(Request $request, $resource)
    {
        switch ($request->getMethod()) {
            case 'GET':
            case 'PATCH':
                return UsersAuthority::canEditUser($this->getUser($request), $resource);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        $item = self::arrayGet($json, 'data');

        if ($item !== null) {
            if (\JsonApi\Schemas\BlubberThread::TYPE !== self::arrayGet($item, 'type')) {
                return 'Wrong `type` in document´s `data`.';
            }

            if (!self::arrayGet($item, 'id')) {
                return 'Missing `id` of document´s `data`.';
            }

            if (self::arrayHas($item, 'attributes')) {
                return 'Document must not have `attributes`.';
            }
        }
    }

    private function validateBlubberThread(\User $user, $json)
    {
        $resourceIdentifier = self::arrayGet($json, 'data');
        $thread = \BlubberThread::find($resourceIdentifier['id']);

        if (!$thread) {
            throw new RecordNotFoundException();
        }

        if (!BlubberAuthority::canShowBlubberThread($user, $thread)) {
            throw new BadRequestException('User is not able to access given thread.');
        }

        return $thread;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \User $resource
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, UserSchema::REL_BLUBBER_DEFAULT_THREAD);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param \User $resource
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, UserSchema::REL_BLUBBER_DEFAULT_THREAD);
    }
}
