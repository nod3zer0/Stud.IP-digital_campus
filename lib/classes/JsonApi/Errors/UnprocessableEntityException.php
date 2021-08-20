<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class UnprocessableEntityException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct($detail = null, array $source = null)
    {
        $error = new Error(null, null, null, 422, null, 'Unprocesssable Entity', $detail, $source);
        parent::__construct($error, 422);
    }
}
