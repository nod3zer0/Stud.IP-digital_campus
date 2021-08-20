<?php

namespace JsonApi\Middlewares\Auth;

use Psr\Http\Message\ResponseInterface as Response;

interface Strategy
{
    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check();

    /**
     * Get the currently authenticated user.
     *
     * @return ?\User
     */
    public function user();

    /**
     * Validate a user's credentials.
     *
     * @param Response $response the current response
     *
     * @return Response the new response containing the challenge
     */
    public function addChallenge(Response $response);
}
