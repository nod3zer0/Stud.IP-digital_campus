<?php

namespace Studip\OAuth2\Exceptions;

class InvalidAuthTokenException extends \AccessDeniedException
{
    /**
     * Create a new InvalidAuthTokenException for different auth tokens.
     *
     * @return static
     */
    public static function different()
    {
        return new static('The provided auth token for the request is different from the session auth token.');
    }
}
