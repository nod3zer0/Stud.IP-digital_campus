<?php

namespace JsonApi\Routes\Events;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserEventsIcal extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        if (!$observedUser = \User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);
        if ($user->id !== $observedUser->id) {
            // absichtlich keine AuthorizationFailedException
            // damit unsichtbare Nutzer nicht ermittelt werden können
            throw new RecordNotFoundException();
        }

        $end = \DateTime::createFromFormat('U', '2114377200');
        $start = new \DateTime();
        $ical_export = new \ICalendarExport();
        $ical = $ical_export->exportCalendarDates($observedUser->id, $start, $end)
              . $ical_export->exportCourseDates($observedUser->id, $start, $end)
              . $ical_export->exportCourseExDates($observedUser->id, $start, $end);
        $content = $ical_export->writeHeader() . $ical . $ical_export->writeFooter();

        $response->getBody()->write($content);

        return $response->withHeader('Content-Type', 'text/calendar')
            ->withHeader('Content-Disposition', 'attachment; ' . encode_header_parameter('filename', 'studip.ics'));
    }
}
