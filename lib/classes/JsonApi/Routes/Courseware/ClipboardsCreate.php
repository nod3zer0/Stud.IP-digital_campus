<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Clipboard as ClipboardSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Create a clipboard.
 * 
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.4
 */
class ClipboardsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);
        if (!Authority::canCreateClipboard($user)) {
            throw new AuthorizationFailedException();
        }
        $object = $this->getObject($json);
        if (!$object) {
            throw new RecordNotFoundException();
        }
        $clipboard = $this->createClipboard($user, $json, $object);

        return $this->getCreatedResponse($clipboard);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (!self::arrayHas($json, 'data.attributes.name')) {
            return 'Missing `name` value.';
        }
        if (!self::arrayHas($json, 'data.attributes.object-type')) {
            return 'Missing `object-type` value.';
        }
        if (
            !(
                self::arrayHas($json, 'data.attributes.block-id')
                || self::arrayHas($json, 'data.attributes.container-id')
                || self::arrayHas($json, 'data.attributes.structural-element-id')
            )
        ) {
            return 'Missing `block-id`, `container-id` or `structural-element-id` value.';
        }
        if (!self::arrayHas($json, 'data.attributes.object-kind')) {
            return 'Missing `object-kind` value.';
        }
        if (!$this->validateObjectType($json)) {
            return 'Invalid `object-type` value.';
        }
    }

    private function createClipboard(\User $user, array $json, $object)
    {
        $clipboard = \Courseware\Clipboard::create([
            'user_id' => $user->id,
            'name' => self::arrayGet($json, 'data.attributes.name'),
            'block_id' => self::arrayGet($json, 'data.attributes.block-id'),
            'container_id' => self::arrayGet($json, 'data.attributes.container-id'),
            'structural_element_id' => self::arrayGet($json, 'data.attributes.structural-element-id'),
            'object_type' => self::arrayGet($json, 'data.attributes.object-type'),
            'object_kind' => self::arrayGet($json, 'data.attributes.object-kind'),
            'backup' => $this->createBackup($object)
        ]);

        return $clipboard;
    }

    private function createBackup($object): string
    {
        return $object->getClipboardBackup();
    }

    private function validateObjectType($json): bool
    {
        $type = self::arrayGet($json, 'data.attributes.object-type');

        return in_array($type, ['courseware-structural-elements', 'courseware-containers', 'courseware-blocks']);
    }

    private function getObject($json): ?object
    {
        $object = null;
        $type = self::arrayGet($json, 'data.attributes.object-type');

        switch ($type) {
            case 'courseware-structural-elements':
                $object = \Courseware\StructuralElement::find(self::arrayGet($json, 'data.attributes.structural-element-id'));
                break;
            case 'courseware-containers':
                $object = \Courseware\Container::find(self::arrayGet($json, 'data.attributes.container-id'));
                break;
            case 'courseware-blocks':
                $object = \Courseware\Block::find(self::arrayGet($json, 'data.attributes.block-id'));
                break;
        }

        return $object;
    }
}

