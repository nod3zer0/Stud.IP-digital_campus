<?php

use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Studip\OAuth2\NegotiatesWithPsr7;

abstract class OAuth2Controller extends StudipController
{
    use NegotiatesWithPsr7;

    /**
     * @return void
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        page_open([
            'sess' => 'Seminar_Session',
            'auth' => 'Seminar_Default_Auth',
            'perm' => 'Seminar_Perm',
            'user' => 'Seminar_User',
        ]);

        $this->set_layout(null);

        $this->container = new Studip\OAuth2\Container();
        $this->server = $this->getAuthorizationServer();
    }

    /**
     * Exception handler called when the performance of an action raises an
     * exception.
     *
     * @param Exception $exception the thrown exception
     */
    public function rescue($exception)
    {
        if ($exception instanceof OAuthServerException) {
            $psrResponse = $exception->generateHttpResponse($this->getPsrResponse());

            return $this->convertPsrResponse($psrResponse);
        }

        return new Trails_Response($exception->getMessage(), [], 500);
    }

    protected function getAuthorizationServer(): AuthorizationServer
    {
        return $this->container->get(AuthorizationServer::class);
    }
}
