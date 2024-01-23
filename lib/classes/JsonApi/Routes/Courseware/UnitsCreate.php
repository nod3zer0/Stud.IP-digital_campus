<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Unit;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\Unit as UnitSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

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
        $range = $this->getRange($json);

        if (!$range) {
            throw new RecordNotFoundException();
        }
        if (!Authority::canCreateUnit($user, $range)) {
            throw new AuthorizationFailedException();
        }
        $struct = $this->createUnit($user, $range, $json);

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

    private function getRange($json): ?\Range
    {
        $rangeData = self::arrayGet($json, 'data.relationships.range.data');

        try {
            return \RangeFactory::createRange(
                $this->getRangeType($rangeData['type']),
                $rangeData['id']
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    private function validateRange($json): bool
    {
        $range = $this->getRange($json);

        return isset($range);
    }

    private function createUnit(\User $user, \Range $range, array $json)
    {
        $struct = \Courseware\StructuralElement::create([
            'parent_id' => null,
            'range_id' => $range->getRangeId(),
            'range_type' => $range->getRangeType(),
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => '',
            'title' => self::arrayGet($json, 'data.attributes.title', ''),
            'purpose' => self::arrayGet($json, 'data.attributes.purpose', ''),
            'payload' => self::arrayGet($json, 'data.attributes.payload', ''),
            'position' => 0,
            'commentable' => 0
        ]);

        $unit = \Courseware\Unit::create([
            'range_id' => $range->getRangeId(),
            'range_type' => $range->getRangeType(),
            'structural_element_id' => $struct->id,
            'content_type' => 'courseware',
            'position' => Unit::getNewPosition($range->getRangeId()),
            'creator_id' => $user->id,
            'public' => self::arrayGet($json, 'data.attributes.public', '0'),
            'release_date' => self::arrayGet($json, 'data.attributes.release-date'),
            'withdraw_date' => self::arrayGet($json, 'data.attributes.withdraw-date'),
        ]);

        $instance = new \Courseware\Instance($struct);

        $instance->setRootLayout(self::arrayGet($json, 'data.attributes.settings.root-layout') ?? 'default');

        $instance->getUnit()->store();

        if (self::arrayGet($json, 'data.template.type') === 'topics') {
            $struct->createChildrenFromCourseTopics();
        }

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
