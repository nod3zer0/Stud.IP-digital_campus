<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class UnsupportedRequestError extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error(null, null, null, 403, null, 'Unsupported request.', $detail, $source);
        parent::__construct($error, 403);
    }
}
