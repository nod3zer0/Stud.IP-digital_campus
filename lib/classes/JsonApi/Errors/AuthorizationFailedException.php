<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

/**
 * TODO.
 */
class AuthorizationFailedException extends JsonApiException
{
    /**
     * TODO.
     */
    public function __construct()
    {
        $error = new Error(null, null, null, '403', null, 'Forbidden');
        parent::__construct($error, 403);
    }
}
