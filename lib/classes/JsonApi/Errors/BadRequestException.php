<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class BadRequestException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error(null, null, null, 400, null, 'Bad Request', $detail, $source);
        parent::__construct($error, 400);
    }
}
