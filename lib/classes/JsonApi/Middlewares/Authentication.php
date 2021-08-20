<?php

namespace JsonApi\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Authentication
{
    // der Schlüssel des Request-Attributs, in dem der Stud.IP-Nutzer
    // gefunden werden kann:

    // $user = $request->getAttribute(Authentication::USER_KEY);
    const USER_KEY = 'studip-user';

    // a callable accepting two arguments username and password and
    // returning either null or a Stud.IP user object
    /** @var callable */
    private $authenticator;

    /**
     * Der Konstruktor.
     *
     * @param callable $authenticator ein Callable, das den Nutzernamen und
     *                                das Passwort als Argumente erhält und
     *                                damit entweder einen Stud.IP-User-Objekt
     *                                oder null zurückgibt
     */
    public function __construct($authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * Hier muss die Autorisierung implementiert werden.
     *
     * @param Request        $request das Request-Objekt
     * @param RequestHandler $handler der PSR-15 Request Handler
     *
     * @return ResponseInterface das neue Response-Objekt
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $guards = [
            new Auth\SessionStrategy(),
            new Auth\HttpBasicAuthStrategy($request, $this->authenticator),
            new Auth\OAuth1Strategy($request, $this->authenticator),
        ];

        foreach ($guards as $guard) {
            if ($guard->check()) {
                $request = $this->provideUser($request, $guard->user());

                return $handler->handle($request);
            }
        }

        return $this->generateChallenges($guards);
    }

    // according to RFC 2616
    private function generateChallenges(array $guards): Response
    {
        $response = new Response(401);

        foreach ($guards as $guard) {
            $response = $guard->addChallenge($response);
        }

        return $response;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function provideUser(Request $request, \User $user): Request
    {
        if ('nobody' === $GLOBALS['user']->id) {
            $GLOBALS['user'] = new \Seminar_User($user);
            $GLOBALS['auth'] = new \Seminar_Auth();
            $GLOBALS['auth']->auth = [
                'uid' => $user->id,
                'uname' => $user->username,
                'perm' => $user->perms,
            ];
            $GLOBALS['perm'] = new \Seminar_Perm();
            $GLOBALS['MAIL_VALIDATE_BOX'] = false;
            $GLOBALS['sess']->delete();
            setTempLanguage($user->id);
        }

        return $request->withAttribute(self::USER_KEY, $user);
    }
}
