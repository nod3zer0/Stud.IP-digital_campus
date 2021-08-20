<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class HttpRangeException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($error = null)
    {
        $errorObject = new Error(null, null, null, 416, null, 'Requested Range Not Satisfiable.', $error);
        parent::__construct($errorObject, 416);
    }
}
