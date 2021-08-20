<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Exceptions\JsonApiException;

class NotAcceptableException extends JsonApiException
{
    public function __construct()
    {
        $error = new Error(null, null, null, '406', null, 'Not Acceptable Error');
        parent::__construct($error, 406);
    }
}
