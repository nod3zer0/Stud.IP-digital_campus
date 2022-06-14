<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\ConsultationSlot;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SlotsByBlockIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        ConsultationSlot::REL_BLOCK,
        ConsultationSlot::REL_BOOKINGS,
    ];
    protected $allowedPagingParameters = ['offset', 'limit'];

    public function __invoke(Request $request, Response $response, $args)
    {
        $block = \ConsultationBlock::find($args['id']);
        if (!$block) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlock($this->getUser($request), $block)) {
            throw new AuthorizationFailedException();
        }

        [$offset, $limit] = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            $block->slots->limit($offset, $limit),
            count($block->slots)
        );
    }
}
