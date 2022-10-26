<?php

namespace Studip\OAuth2\Exceptions;

class InvalidAuthTokenException extends \AccessDeniedException
{
    /**
     * Create a new InvalidAuthTokenException for different auth tokens.
     *
     * @return InvalidAuthTokenException
     */
    public static function different()
    {
        return new InvalidAuthTokenException('The provided auth token for the request is different from the session auth token.');
    }
}
