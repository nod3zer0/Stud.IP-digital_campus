<?php

namespace Studip\OAuth2\Exceptions;

use League\OAuth2\Server\Exception\OAuthServerException;

class SetupError extends OAuthServerException
{
    public function __construct()
    {
        $message = _('Das OAuth2-Setup dieser Stud.IP-Installation ist fehlerhaft.');

        parent::__construct($message, 500, 'invalid_setup', 500);
    }
}
