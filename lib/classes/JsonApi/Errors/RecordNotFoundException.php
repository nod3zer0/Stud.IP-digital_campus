<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class RecordNotFoundException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $errorObject = new Error(null, null, null, 404, null, 'Not Found', $error);
        parent::__construct($errorObject, 404);
    }
}
