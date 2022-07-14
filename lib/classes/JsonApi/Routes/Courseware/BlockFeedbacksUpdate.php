<?php

namespace JsonApi\Routes\Courseware;

use Courseware\BlockFeedback;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Block as BlockSchema;
use JsonApi\Schemas\Courseware\BlockFeedback as BlockFeedbackSchema;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update a feedback on a block
 */
class BlockFeedbacksUpdate extends JsonApiController
{
    use ValidationTrait, UserProgressesHelper;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = BlockFeedback::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateBlockFeedback($this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $blockFeedback = $this->updateBlockFeedback($json, $resource);

        return $this->getContentResponse($blockFeedback);
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
        if (BlockFeedbackSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (self::arrayGet($json, 'data.id') !== $data->id) {
            return 'Mismatch in document `id`.';
        }

        if (!($feedback = self::arrayGet($json, 'data.attributes.feedback'))) {
            return 'Missing `feedback` attribute.';
        }
        if (!is_string($feedback)) {
            return 'Attribute `feedback` must be a string.';
        }
        if ($feedback == '') {
            return 'Attribute `feedback` must not be empty.';
        }

        if (self::arrayHas($json, 'data.relationships.user')) {
            if (!($user = $this->getUserFromJson($json))) {
                return 'Invalid `user` relationship.';
            }
            if ($user->id !== $data['user_id']) {
                return 'Cannot update `user` relationship.';
            }
        }

        if (self::arrayHas($json, 'data.relationships.block')) {
            if (!($block = $this->getBlockFromJson($json))) {
                return 'Invalid `block` relationship.';
            }
            if ($block->id !== $data['block_id']) {
                return 'Cannot update `block` relationship.';
            }
        }
    }

    private function getBlockFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.block', BlockSchema::TYPE)) {
            return null;
        }
        $blockId = self::arrayGet($json, 'data.relationships.block.data.id');

        return \Courseware\Block::find($blockId);
    }

    private function getUserFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.user', UserSchema::TYPE)) {
            return null;
        }
        $userId = self::arrayGet($json, 'data.relationships.user.data.id');

        return \User::find($userId);
    }

    private function updateBlockFeedback(array $json, \Courseware\BlockFeedback $resource)
    {
        $resource->feedback = self::arrayGet($json, 'data.attributes.feedback', '');
        $resource->store();

        return $resource;
    }
}
