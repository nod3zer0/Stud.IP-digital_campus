<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Unit as UnitSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\Activity\Activity;

/**
 * Create a block in a container.
 */
class UnitsCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);
        $user = $this->getUser($request);
        if (!Authority::canCreateUnit($user)) {
            throw new AuthorizationFailedException();
        }
        $struct = $this->createUnit($user, $json);

        return $this->getCreatedResponse($struct);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }
        if (UnitSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }
        if (!self::arrayHas($json, 'data.attributes.title')) {
            return 'Missing `title` value.';
        }
        if (!self::arrayHas($json, 'data.attributes.payload.description')) {
            return 'Missing `description` value.';
        }
        if (!self::arrayHas($json, 'data.relationships.range')) {
            return 'Missing `range` relationship.';
        }
        if (!$this->validateRange($json)) {
            return 'Invalid `range` relationship.';
        }
    }

    private function validateRange($json): bool
    {
        $rangeData = self::arrayGet($json, 'data.relationships.range.data');

        if (!in_array($rangeData['type'], ['courses','users'])) {
            return false;
        }
        if ($rangeData['type'] ===  'courses') {
            $range = \Course::find($rangeData['id']);
        } else {
            $range = \User::find($rangeData['id']);
        }

        return isset($range);
    }

    private function createUnit(\User $user, array $json)
    {
        $range_id = self::arrayGet($json, 'data.relationships.range.data.id');
        $range_type = self::getRangeType(self::arrayGet($json, 'data.relationships.range.data.type'));

        $struct = \Courseware\StructuralElement::build([
            'parent_id' => null,
            'range_id' => $range_id,
            'range_type' => $range_type,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => '',
            'title' => self::arrayGet($json, 'data.attributes.title', ''),
            'purpose' => self::arrayGet($json, 'data.attributes.purpose', ''),
            'payload' => self::arrayGet($json, 'data.attributes.payload', ''),
            'position' => 0
        ]);

        $struct->store();

        $unit = \Courseware\Unit::build([
            'range_id' => $range_id,
            'range_type' => $range_type,
            'structural_element_id' => $struct->id,
            'content_type' => 'courseware',
            'creator_id' => $user->id,
            'public' => self::arrayGet($json, 'data.attributes.public', '0'),
            'release_date' => self::arrayGet($json, 'data.attributes.release-date'),
            'withdraw_date' => self::arrayGet($json, 'data.attributes.withdraw-date'),
        ]);

        $unit->store();

        return $unit;
    }

    private function getRangeType($type): ?string
    {
        $type_map = [
            'courses' => 'course',
            'users'   => 'user',
        ];

        return $type_map[$type] ?? null;
    }
}
