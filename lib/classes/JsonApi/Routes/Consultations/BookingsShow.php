<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\ConsultationBooking;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingsShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        ConsultationBooking::REL_SLOT,
        ConsultationBooking::REL_USER,
    ];

    public function __invoke(Request $request, Response $response, $args)
    {
        $booking = \ConsultationBooking::find($args['id']);
        if (!$booking) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBooking($this->getUser($request), $booking)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($booking);
    }
}
