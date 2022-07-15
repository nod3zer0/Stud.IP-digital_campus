<?php

namespace JsonApi\Middlewares\Auth;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Studip\OAuth2\Container;
use Studip\OAuth2\Models\AccessToken;
use Studip\OAuth2\Models\Client;

class OAuth2Strategy implements Strategy
{
    /** @var callable */
    protected $authenticator;

    /** @var Request */
    protected $request;

    /** @var ?\User */
    protected $user;

    /**
     * @param callable $authenticator
     */
    public function __construct(Request $request, $authenticator)
    {
        $this->request = $request;
        $this->authenticator = $authenticator;
    }

    public function check()
    {
        return !is_null($this->user());
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $this->user = $this->detect();

        return $this->user;
    }

    public function addChallenge(Response $response)
    {
        return $response->withHeader('Authorization', '');
    }

    private function detect(): ?\User
    {
        $bearerToken = $this->bearerToken($this->request);
        if (!$bearerToken) {
            return null;
        }

        $container = new Container();
        $server = $container->get(ResourceServer::class);

        try {
            $psrRequest = $server->validateAuthenticatedRequest($this->request);

            $userId = $psrRequest->getAttribute('oauth_user_id');
            $user = \User::find($userId);
            if (!$user) {
                return null;
            }

            $clientId = $psrRequest->getAttribute('oauth_client_id');
            if (Client::revoked($clientId)) {
                return null;
            }

            return $user;
        } catch (OAuthServerException $oauthException) {
            // TODO: reporting?
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function bearerToken(Request $request)
    {
        if ($request->hasHeader('Authorization')) {
            $header = $request->getHeaderLine('Authorization');
            $position = strrpos($header, 'Bearer ');
            if ($position !== false) {
                $header = substr($header, $position + 7);

                return strpos($header, ',') !== false ? strstr($header, ',', true) : $header;
            }
        }

        return null;
    }
}
