<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookingsDelete extends JsonApiController
{
    use ValidationTrait;

    public function __invoke(Request $request, Response $response, $args)
    {
        $body = (string) $request->getBody();
        if ($body) {
            $json = $this->validate($request);
        } else {
            $json = [];
        }

        $booking = \ConsultationBooking::find($args['id']);
        if (!$booking) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);
        if (!Authority::canEditBooking($user, $booking)) {
            throw new AuthorizationFailedException();
        }

        $reason = self::arrayGet($json, 'data.attributes.reason', '');

        $booking->cancel($reason);

        return $this->getCodeResponse(204);
    }

    protected function validateResourceDocument($json, $data)
    {
    }
}
