<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

class UnsupportedMediaTypeException extends JsonApiException
{
    public function __construct()
    {
        $error = new Error(null, null, null, '415', null, 'Unsupported Media Type Error');
        parent::__construct($error, 415);
    }
}
