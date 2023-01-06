<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Instance;
use Courseware\StructuralElement;
use Courseware\Unit;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;

trait CoursewareInstancesHelper
{
    private function findInstance(string $instanceId): Instance
    {
        [$rangeType, $rangeId] = explode('_', $instanceId);
        if (!is_string($rangeType) || !is_string($rangeId)) {
            throw new BadRequestException('Invalid instance id: "' . $instanceId . '".');
        }

        return $this->findInstanceWithRange($rangeType, $rangeId);
    }

    private function findInstanceWithRange(string $rangeType, string $rangeId): Instance
    {
        $methods = [
            'course' => 'getCoursewareCourse',
            'courses' => 'getCoursewareCourse',
            'user' => 'getCoursewareUser',
            'users' => 'getCoursewareUser',
            'sharedusers' => 'getSharedCoursewareUser',
        ];
        if (!($method = $methods[$rangeType])) {
            throw new BadRequestException('Invalid range type: "' . $rangeType . '".');
        }
        $root = null;
        if ($rangeType !== 'sharedusers') {
            $chunks = explode('_', $rangeId);
            $courseId = $chunks[0];
            $unitId = $chunks[1] ?? null;

            if ($unitId) {
                $unit = Unit::findOneBySQL('range_id = ? AND id = ?', [$courseId, $unitId]);
            } else {
                $unit = Unit::findOneBySQL('range_id = ?', [$courseId]);
            }
            $root = $unit->structural_element;
        } else {
            $root = StructuralElement::$method($rangeId);
        }
        if (!$root) {
            throw new RecordNotFoundException();
        }

        return new Instance($root);
    }
}
