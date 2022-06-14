<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\ConsultationBlock;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BlockShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        ConsultationBlock::REL_SLOTS,
        ConsultationBlock::REL_RANGE,
    ];

    public function __invoke(Request $request, Response $response, $args)
    {
        $block = \ConsultationBlock::find($args['id']);
        if (!$block) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowBlock($this->getUser($request), $block)) {
            throw new AuthorizationFailedException();
        }

        return $this->getContentResponse($block);
    }
}
