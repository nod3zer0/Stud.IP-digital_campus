<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Unit;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update multiple Unit positions.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.5
 */

class UnitsSort extends JsonApiController
{
    public function __invoke(Request $request, Response $response, $args)
    {
        $range = $this->getRange($args);
        $user = $this->getUser($request);

        if (!Authority::canSortUnit($user, $range)) {
            throw new AuthorizationFailedException();
        }
        $data = $request->getParsedBody()['data'];
        $positions = $data['positions'];
        $unitCount = Unit::getNewPosition($range->id);

        if (count($positions) !== $unitCount) {
            throw new BadRequestException('Fehler beim Sortieren der Lernmaterialien.');
        }

        Unit::updatePositions($range, $positions);

        $response = $response->withHeader('Content-Type', 'application/json');

        return $response;
    }

    private function getRange($args): ?\Range
    {
        try {
            return \RangeFactory::createRange(
                $this->getRangeType($args['type']),
                $args['id']
            );
        } catch (\Exception $e) {
            return null;
        }
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