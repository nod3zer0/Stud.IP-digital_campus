<?php

namespace JsonApi\Middlewares\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HttpBasicAuthStrategy implements Strategy
{
    /** @var callable */
    protected $authenticator;

    /** @var Request */
    protected $request;

    /** @var ?\User */
    protected $user;

    /**
     * @param Request $request
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

        $serverParams = $this->request->getServerParams();

        if (isset($serverParams['PHP_AUTH_USER'], $serverParams['PHP_AUTH_PW'])) {
            $authenticator = $this->authenticator;
            $this->user = $authenticator($serverParams['PHP_AUTH_USER'], $serverParams['PHP_AUTH_PW']);
        }

        return $this->user;
    }

    public function addChallenge(Response $response)
    {
        return $response->withHeader('WWW-Authenticate', sprintf('Basic realm="%s"', 'Stud.IP JSON-API'));
    }
}
