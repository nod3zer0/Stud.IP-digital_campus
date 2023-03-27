<?php

namespace Helper;

use DI\ContainerBuilder;
use JsonApi\Errors\JsonApiErrorRenderer;
use JsonApi\Middlewares\Authentication;
use Psr\Http\Message\ResponseInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Request;
use WoohooLabs\Yang\JsonApi\Request\JsonApiRequestBuilder;
use WoohooLabs\Yang\JsonApi\Response\JsonApiResponse;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Jsonapi extends \Codeception\Module
{
    /**
     * @param array    $credentials
     * @param callable $function
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function withPHPLib($credentials, $function)
    {
        // EVIL HACK
        $oldPerm = $GLOBALS['perm'];
        $oldUser = $GLOBALS['user'];
        $GLOBALS['perm'] = new \Seminar_Perm();
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $result = $function($credentials);

        // EVIL HACK
        $GLOBALS['user'] = $oldUser;
        $GLOBALS['perm'] = $oldPerm;

        return $result;
    }

    /**
     * @param array    $credentials
     * @param string   $method
     * @param string   $pattern
     * @param callable $callable
     * @param ?string  $name
     *
     * @return \Slim\App
     */
    public function createApp($credentials, $method, $pattern, $callable, $name = null)
    {
        return $this->createApp0($credentials, function ($app) use ($method, $pattern, $callable, $name) {
            $route = $app->map([strtoupper($method)], $pattern, $callable);
            if (isset($name)) {
                $route->setName($name);
            }
        });
    }

    /**
     * @param array|null $credentials
     *
     * @return JsonApiRequestBuilder
     */
    public function createRequestBuilder($credentials = null)
    {
        $serverParams = [];
        if ($credentials) {
            $serverParams = [
                'PHP_AUTH_USER' => $credentials['username'],
                'PHP_AUTH_PW' => $credentials['password'],
            ];
        }
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest('GET', '', $serverParams);

        $requestBuilder = new JsonApiRequestBuilder($request);

        $requestBuilder->setProtocolVersion('1.0')->setHeader('Accept-Charset', 'utf-8');

        return $requestBuilder;
    }

    /**
     * @param \Slim\App $app
     *
     * @return JsonApiResponse
     */
    public function sendMockRequest($app, Request $request)
    {
        /** @var \DI\Container */
        $container = $app->getContainer();
        $container->set('request', $request);

        $response = $app->handle($request);

        return new JsonApiResponse($response);
    }

    /**
     * @param array|null $credentials
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function createApp0($credentials, callable $routerFn): \Slim\App
    {
        $app = $this->appFactory();

        $group = $app->group('', $routerFn);

        if ($credentials) {
            $authenticator = function ($username, $password) use ($credentials) {
                // must return a \User
                if ($username === $credentials['username'] && $password === $credentials['password']) {
                    $user = \User::find($credentials['id']);

                    return $user;
                }

                return null;
            };

            $group
                ->add(function ($request, $handler) {
                    $user = $request->getAttribute(Authentication::USER_KEY, null);

                    $GLOBALS['auth'] = new \Seminar_Auth();
                    $GLOBALS['auth']->auth = [
                        'uid' => $user->id,
                        'uname' => $user->username,
                        'perm' => $user->perms,
                    ];
                    $GLOBALS['user'] = new \Seminar_User($user->id);
                    $GLOBALS['perm'] = new \Seminar_Perm();
                    $GLOBALS['MAIL_VALIDATE_BOX'] = false;
                    $y = new \Seminar_User($user->id);
                    $x = $y->getAuthenticatedUser();


                    $dbManager = \DBManager::get();
                    $stmt = $dbManager->prepare("SELECT * FROM auth_user_md5 LEFT JOIN user_info USING (user_id) WHERE user_id = ?");
                    $stmt->execute([$user->id]);

                    return $handler->handle($request);
                })
                ->add(new Authentication($authenticator));
        }

        return $app;
    }

    private function appFactory(): \Slim\App
    {
        $containerBuilder = new ContainerBuilder();

        $settings = require 'lib/classes/JsonApi/settings.php';
        $settings($containerBuilder);

        $dependencies = require 'lib/classes/JsonApi/dependencies.php';
        $dependencies($containerBuilder);

        // Build PHP_DI Container
        $container = $containerBuilder->build();

        // Instantiate the app
        AppFactory::setContainer($container);
        $app = AppFactory::create();
        $container->set(\Slim\App::class, $app);

        // Register middleware
        $middleware = require 'lib/classes/JsonApi/middleware.php';
        $middleware($app);

        // Add Error Middleware
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);
        $errorMiddleware->setDefaultErrorHandler(new \JsonApi\Errors\ErrorHandler($app));

        return $app;
    }

    public function storeJsonMD(
        string $filename,
        ResponseInterface $response,
        int $limit = null,
        string $ellipsis = null
    ): string {
        $body = "{$response->getBody()}";
        $body = preg_replace('!plugins.php\\\\/argonautsplugin!', 'https:\\/\\/example.com', $body);
        $body = preg_replace('!\\\\/!', '/', $body);
        $body = preg_replace(['!%5B!', '!%5D!'], ['[', ']'], $body);

        $jsonBody = json_decode($body, true);

        if ($limit && isset($jsonBody['data']) && is_array($jsonBody['data'])) {
            $jsonBody['data'] = array_slice($jsonBody['data'], 0, $limit);
            if ($ellipsis) {
                $jsonBody['data'][] = $ellipsis;
            }
        }

        $json = json_encode($jsonBody, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // This converts the default indentation of 4 spaces to 2 spaces
        $json = preg_replace_callback('/^( +)(?=\S)/m', function ($match) {
            return str_pad('', strlen($match[1]) / 2, ' ');
        }, $json);

        $dirname = codecept_output_dir() . 'json-for-slate/';
        if (!file_exists($dirname)) {
            @mkdir($dirname);
        }

        if (file_exists($dirname)) {
            if ('.md' !== substr($filename, -3)) {
                $filename .= '.md';
            }
            if ('_' !== $filename[0]) {
                $filename = '_' . $filename;
            }
            file_put_contents($dirname . $filename, "```json\n" . $json . "\n```\n");
        }

        return $json;
    }
}
