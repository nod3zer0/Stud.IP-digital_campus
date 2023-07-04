<?php

namespace JsonApi\Routes\StockImages;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all stock images.
 */
class StockImagesIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response): Response
    {
        if (!Authority::canIndexStockImages($this->getUser($request))) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $total = \StockImage::countBySQL('1');
        $stockImages = \StockImage::findBySQL("1 ORDER BY title ASC LIMIT {$offset}, {$limit}");

        return $this->getPaginatedContentResponse($stockImages, $total);
    }
}
