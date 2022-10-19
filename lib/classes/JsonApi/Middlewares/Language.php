<?php
namespace JsonApi\Middlewares;

use Negotiation\LanguageNegotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * This class defines a middleware that tries to set the language for Stud.IP
 * by analyzing the HTTP header "Accept-Language".
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
final class Language implements MiddlewareInterface
{
    /**
     * @param Request        $request das Request-Objekt
     * @param RequestHandler $handler der PSR-15 Request Handler
     *
     * @return ResponseInterface das neue Response-Objekt
     */
    public function process(Request $request, RequestHandler $handler): ResponseInterface
    {
        $language = $_SESSION['_language'] ?? get_accepted_languages($request);

        init_i18n($language);
        $_SESSION['_language'] = $language;

        return $handler->handle($request);
    }
}
