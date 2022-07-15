<?php
require_once __DIR__ . '/oauth2_controller.php';

class Api_Oauth2_TokenController extends OAuth2Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ('index' !== $action) {
            throw new Trails_Exception(404);
        }

        if (!Request::isPost()) {
            throw new Trails_Exception(405);
        }

        $action = 'issue_token';
    }

    public function issue_token_action(): void
    {
        $psrRequest = $this->getPsrRequest();
        $psrResponse = $this->getPsrResponse();
        $response = $this->server->respondToAccessTokenRequest($psrRequest, $psrResponse);

        $this->renderPsrResponse($response);
    }
}
