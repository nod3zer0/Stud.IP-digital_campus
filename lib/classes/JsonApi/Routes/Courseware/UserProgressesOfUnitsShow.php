<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\Instance;
use Courseware\StructuralElement;
use Courseware\Unit;
use Courseware\UserProgress;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays the progress of a user for a unit
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.3
 */

class UserProgressesOfUnitsShow extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $user = $this->getUser($request);
        $unit = Unit::find($args['id']);
        if (!$unit) {
            throw new RecordNotFoundException();
        }
        $root = $unit->structural_element;
        if (!$GLOBALS['perm']->have_studip_perm('autor', $root->range_id) || !$unit->canRead($user)) {
            throw new AuthorizationFailedException();
        }
        $instance = new Instance($root);
        $isTeacher = $GLOBALS['perm']->have_studip_perm('tutor', $root->range_id);

        $elements = $this->findElements($instance, $user);

        $progress = $this->computeSelfProgresses($instance, $user, $elements, $isTeacher);
        $progress = $this->computeCumulativeProgresses($instance, $elements, $progress);

        $progresses = $this->prepareProgressData($elements, $progress);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) json_encode($progresses));

        return $response;
    }

    private function findElements(Instance $instance, \User $user): iterable
    {
        $elements = $instance->getRoot()->findDescendants($user);
        $elements[] = $instance->getRoot();

        return array_combine(array_column($elements, 'id'), $elements);
    }

    private function computeSelfProgresses(
        Instance $instance,
        \User $user,
        iterable &$elements,
        bool $showProgressForAllParticipants
    ): iterable
    {
        $progress = [];
        /** @var \Course $course */
        $course = $instance->getRange();
        $allBlockIds = $instance->findAllBlocksGroupedByStructuralElementId(function ($row) {
            return $row['id'];
        });
        $courseMemberIds = $showProgressForAllParticipants
            ? array_column($course->getMembersWithStatus('autor'), 'user_id')
            : [$user->getId()];

        $sql = "SELECT block_id, COUNT(grade) AS count, SUM(grade) AS grade
                FROM cw_user_progresses
                WHERE block_id IN (?) AND user_id IN (?)
                GROUP BY block_id";

        $userProgresses = \DBManager::get()->fetchGrouped($sql, [$allBlockIds, $courseMemberIds]);

        foreach ($elements as $elementId => $element) {
            $selfProgress = $this->getSelfProgresses($allBlockIds, $elementId, $userProgresses, $courseMemberIds);
            $progress[$elementId] = [
                'self' => $selfProgress['counter'] ? $selfProgress['progress'] / $selfProgress['counter'] : 1,
            ];
        }

        return $progress;
    }

    private function getSelfProgresses(
        array $allBlockIds,
        string $elementId,
        array $userProgresses,
        array $courseMemberIds
    ): array {
        $blks = $allBlockIds[$elementId] ?? [];
        if (count($blks) === 0) {
            return [
                'counter' => 0,
                'progress' => 1,
            ];
        }

        $data = [
            'counter' => count($blks),
            'progress' => 0,
        ];

        $usersCounter = count($courseMemberIds);
        foreach ($blks as $blk) {
            $progresses = $userProgresses[$blk];
            $usersProgress = $progresses['count'] ? (float) $progresses['grade'] : 0;
            $data['progress'] += $usersCounter > 0 ? $usersProgress / $usersCounter : 0;
        }

        return $data;
    }

    private function computeCumulativeProgresses(Instance $instance, iterable &$elements, iterable &$progress): iterable
    {
        $childrenOf = $this->computeChildrenOf($elements);

        // compute `cumulative` of each element
        $visitor = function (&$progress, $element) use (&$childrenOf, &$elements, &$visitor) {
            $elementId = $element->getId();
            $numberOfNodes = 0;
            $cumulative = 0;

            // visit children first
            if (isset($childrenOf[$elementId])) {
                foreach ($childrenOf[$elementId] as $childId) {
                    $visitor($progress, $elements[$childId]);
                    $numberOfNodes += $progress[$childId]['numberOfNodes'];
                    $cumulative += $progress[$childId]['cumulative'];
                }
            }

            $progress[$elementId]['cumulative'] = $cumulative + $progress[$elementId]['self'];
            $progress[$elementId]['numberOfNodes'] = $numberOfNodes + 1;

            return $progress;
        };

        $visitor($progress, $instance->getRoot());

        return $progress;
    }

    private function computeChildrenOf(iterable &$elements): iterable
    {
        $childrenOf = [];
        foreach ($elements as $elementId => $element) {
            if ($element['parent_id']) {
                if (!isset($childrenOf[$element['parent_id']])) {
                    $childrenOf[$element['parent_id']] = [];
                }
                $childrenOf[$element['parent_id']][] = $elementId;
            }
        }

        return $childrenOf;
    }

    private function prepareProgressData(iterable &$elements, iterable &$progress): iterable
    {
        $data = [];
        foreach ($elements as $elementId => $element) {
            $elementProgress = $progress[$elementId];
            $cumulative = $elementProgress['cumulative'] / $elementProgress['numberOfNodes'];

            $data[$elementId] = [
                'id' => (int) $elementId,
                'parent_id' => (int) $element['parent_id'],
                'name' => $element['title'],
                'progress' => [
                    'cumulative' => round($cumulative, 2) * 100,
                    'self' => round($elementProgress['self'], 2) * 100,
                ],
            ];
        }

        return $data;
    }
}
