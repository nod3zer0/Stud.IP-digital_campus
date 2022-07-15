<?php

namespace Studip\OAuth2\Models;

trait RevokedHelper
{
    /**
     * @return bool
     */
    public function isRevoked()
    {
        return (bool) $this->revoked;
    }

    /**
     * Revoke the token instance.
     *
     * @return void
     */
    public function revoke()
    {
        $this->revoked = 1;
        $this->store();
    }
}
