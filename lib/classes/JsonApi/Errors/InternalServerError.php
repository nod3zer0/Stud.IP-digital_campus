<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class InternalServerError extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error(null, null, null, 500, null, 'Internal Server Error', $detail, $source);
        parent::__construct($error, 500);
    }
}
