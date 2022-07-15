<?php

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use League\OAuth2\Server\AuthorizationServer;

use Studip\OAuth2\AccessTokenRepository;
use Studip\OAuth2\AuthCodeRepository;
use Studip\OAuth2\ClientRepository;
use Studip\OAuth2\ScopeRepository;
use Studip\OAuth2\UserEntity;

require '../lib/bootstrap.php';
require '../composer/autoload.php';

function addRoutes(\Slim\App $app, AuthorizationServer $server): void
{
    $app->get('/authorize', function (Request $request, Response $response) use ($server) {
        try {
            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $server->validateAuthorizationRequest($request);

            // The auth request object can be serialized and saved into a user's session.
            // You will probably want to redirect the user at this point to a login endpoint.
            $_SESSION['oauth2_auth_request'] = serialize($authRequest);
            var_dump($_SESSION['oauth2_auth_request']);exit;


            // Once the user has logged in set the user on the AuthorizationRequest
            $authRequest->setUser(new UserEntity()); // an instance of UserEntityInterface

            // At this point you should redirect the user to an authorization page.
            // This form will ask the user to approve the client and the scopes requested.

            // Once the user has approved or denied the client update the status
            // (true = approved, false = denied)
            $authRequest->setAuthorizationApproved(true);

            // Return the HTTP redirect response
            return $server->completeAuthorizationRequest($authRequest, $response);
        } catch (OAuthServerException $exception) {
            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($response);
        } catch (\Exception $exception) {
            // Unknown exception
            $body = new Stream(fopen('php://temp', 'r+'));
            $body->write($exception->getMessage());
            return $response->withStatus(500)->withBody($body);
        }
    });
}

$clientRepository = new ClientRepository(); // instance of ClientRepositoryInterface
$scopeRepository = new ScopeRepository(); // instance of ScopeRepositoryInterface
$accessTokenRepository = new AccessTokenRepository(); // instance of AccessTokenRepositoryInterface
$authCodeRepository = new AuthCodeRepository(); // instance of AuthCodeRepositoryInterface
$refreshTokenRepository = new RefreshTokenRepository(); // instance of RefreshTokenRepositoryInterface

$privateKey = 'file://path/to/private.key';
//$privateKey = new CryptKey('file://path/to/private.key', 'passphrase'); // if private key has a pass phrase
$encryptionKey = 'lxZFUEsBCJ2Yb14IF2ygAHI5N4+ZAUXXaSeeJm6+twsUmIen'; // generate using base64_encode(random_bytes(32))


// Setup the authorization server
$server = new \League\OAuth2\Server\AuthorizationServer(
    $clientRepository,
    $accessTokenRepository,
    $scopeRepository,
    $privateKey,
    $encryptionKey
);

page_open([
    'sess' => 'Seminar_Session',
    'auth' => 'Seminar_Default_Auth',
    'perm' => 'Seminar_Perm',
    'user' => 'Seminar_User',
]);

// Set base url for URLHelper class
URLHelper::setBaseUrl($GLOBALS['CANONICAL_RELATIVE_PATH_STUDIP']);

$containerBuilder = new ContainerBuilder();
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();
$container->set(\Slim\App::class, $app);

$app->setBasePath('/oauth2.php');

$app->addRoutingMiddleware();
addRoutes($app, $server);

$displayErrors = false;
if (defined('\\Studip\\ENV')) {
    $displayErrors = constant('\\Studip\\ENV') === 'development';
}
$logError = true;
$logErrorDetails = true;
$errorMiddleware = $app->addErrorMiddleware($displayErrors, $logError, $logErrorDetails);

$app->run();
