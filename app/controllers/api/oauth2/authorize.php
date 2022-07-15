<?php
require_once __DIR__ . '/oauth2_controller.php';

use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Studip\OAuth2\Bridge\UserEntity;
use Studip\OAuth2\Exceptions\InvalidAuthTokenException;
use Studip\OAuth2\Models\Scope;

class Api_Oauth2_AuthorizeController extends OAuth2Controller
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ('index' !== $action) {
            throw new Trails_Exception(404);
        }

        $action = $this->determineAction();
    }

    private function determineAction(): string
    {
        $method = $this->getMethod();

        if (Request::submitted('auth_token')) {
            $GLOBALS['auth']->login_if('nobody' === $GLOBALS['user']->id);
            CSRFProtection::verifyUnsafeRequest();

            switch ($method) {
                case 'POST':
                    return 'approved';

                case 'DELETE':
                    return 'denied';
            }
        }

        return 'authorize';
    }

    public function authorize_action(): void
    {
        $psrRequest = $this->getPsrRequest();
        $authRequest = $this->server->validateAuthorizationRequest($psrRequest);

        $scopes = $authRequest->getScopes();
        $client = $authRequest->getClient();

        $authToken = randomString(32);
        $this->freezeSessionVars($authRequest, $authToken);

        // show login form if not logged in
        $authPlugin = Config::get()->getValue('API_OAUTH_AUTH_PLUGIN');
        if ('nobody' === $GLOBALS['user']->id && 'Standard' !== $authPlugin && !Request::option('sso')) {
            $queryParams = $psrRequest->getQueryParams();
            $queryParams['sso'] = strtolower($authPlugin);
            $this->redirect($this->authorizeURL($queryParams));

            return;
        } else {
            $GLOBALS['auth']->login_if('nobody' === $GLOBALS['user']->id);
        }

        $this->client = $client;
        $this->user = $GLOBALS['user'];
        $this->scopes = $this->scopesFor($scopes);
        $this->authToken = $authToken;
        $this->state = $authRequest->getState();

        PageLayout::disableHeader();
        $this->render_template(
            'api/oauth2/authorize.php',
            $GLOBALS['template_factory']->open('layouts/base.php')
        );
    }

    public function approved_action(): void
    {
        [$authRequest, $authToken] = $this->thawSessionVars();
        $this->assertValidAuthToken($authToken);

        $authRequest->setUser(new UserEntity($GLOBALS['user']->id));
        $authRequest->setAuthorizationApproved(true);

        $response = $this->server->completeAuthorizationRequest($authRequest, $this->getPsrResponse());

        $this->renderPsrResponse($response);
    }

    public function denied_action(): void
    {
        [$authRequest, $authToken] = $this->thawSessionVars();
        $this->assertValidAuthToken($authToken);

        $authRequest->setUser(new UserEntity($GLOBALS['user']->id));
        $authRequest->setAuthorizationApproved(false);

        $clientUris = $authRequest->getClient()->getRedirectUri();

        $uri = $authRequest->getRedirectUri();
        if (!in_array($uri, $clientUris)) {
            $uri = current($clientUris);
        }

        $uri = URLHelper::getURL($uri, [
            'error' => 'access_denied',
            'state' => Request::get('state'),
        ], true);
        $this->redirect($uri);
    }

    private function getMethod(): string
    {
        $method = Request::method();
        if ('POST' === $method && Request::submitted('_method')) {
            $_method = strtoupper(Request::get('_method'));
            if (in_array($_method, ['DELETE', 'PATCH', 'PUT'])) {
                $method = $_method;
            }
        }

        return $method;
    }

    /**
     * Make sure the auth token matches the one in the session.
     *
     * @throws InvalidAuthTokenException
     */
    private function assertValidAuthToken(string $authToken): void
    {
        if (Request::submitted('auth_token') && $authToken !== Request::get('auth_token')) {
            throw InvalidAuthTokenException::different();
        }
    }

    private function freezeSessionVars(AuthorizationRequest $authRequest, string $authToken): void
    {
        $_SESSION['oauth2'] = [
            'authRequest' => serialize($authRequest),
            'authToken'   => $authToken,
        ];
    }

    private function thawSessionVars(): array
    {
        $authRequest = null;
        $authToken = null;
        if (
            isset($_SESSION['oauth2']) &&
            is_array($_SESSION['oauth2']) &&
            isset($_SESSION['oauth2']['authRequest']) &&
            isset($_SESSION['oauth2']['authToken'])
        ) {
            $authRequest = unserialize($_SESSION['oauth2']['authRequest']);
            $authToken = $_SESSION['oauth2']['authToken'];
        }

        return [$authRequest, $authToken];
    }

    private function scopesFor(array $scopeEntities): array
    {
        $scopes = Scope::scopes();
        $scopeModels = [];
        foreach ($scopeEntities as $scopeEntity) {
            if (isset($scopes[$scopeEntity->getIdentifier()])) {
                $scopeModels[] = $scopes[$scopeEntity->getIdentifier()];
            }
        }

        return $scopeModels;
    }
}
