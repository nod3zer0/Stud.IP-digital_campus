<?php

namespace Studip\OAuth2\Bridge;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use ClientTrait;
    use EntityTrait;

    /**
     * @param string          $identifier
     * @param string          $name
     * @param string|string[] $redirectUri
     * @param bool $isConfidential
     */
    public function __construct($identifier, $name, $redirectUri, $isConfidential)
    {
        $this->identifier = $identifier;
        $this->name = $name;
        $this->redirectUri = $redirectUri;
        $this->isConfidential = $isConfidential;
    }
}
