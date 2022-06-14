<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\ConsultationSlot;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlotShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        ConsultationSlot::REL_BLOCK,
        ConsultationSlot::REL_BOOKINGS,
    ];

    public function __invoke(Request $request, Response $response, $args)
    {
        $slot = \ConsultationSlot::find($args['id']);
        if (!$slot) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowSlot($this->getUser($request), $slot)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($slot);
    }
}
