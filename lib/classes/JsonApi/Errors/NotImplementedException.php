<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class NotImplementedException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error(null, null, null, 501, null, 'Not Implemented Error', $detail, $source);
        parent::__construct($error, 501);
    }
}
