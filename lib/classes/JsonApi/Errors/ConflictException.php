<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class ConflictException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $errorObject = new Error(null, null, null, 409, null, 'Conflict', $error);
        parent::__construct($errorObject, 409);
    }
}
