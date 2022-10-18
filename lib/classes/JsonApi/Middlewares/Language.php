<?php
namespace JsonApi\Middlewares;

use Negotiation\LanguageNegotiator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * This class defines a middleware that tries to set the language for Stud.IP
 * by analyzing the HTTP header "Accept-Language".
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 */
class Language
{
    /**
     * @param Request        $request das Request-Objekt
     * @param RequestHandler $handler der PSR-15 Request Handler
     *
     * @return ResponseInterface das neue Response-Objekt
     */
    public function __invoke(Request $request, RequestHandler $handler)
    {
        $language = $this->detectValidLanguageFromRequest($request);
        $language_before = false;

        // Set language if detected
        if ($language) {
            $language_before = $_SESSION['_language'];
            $_SESSION['_language'] = $language;
            setTempLanguage(false, $language);
        }

        $response =  $handler->handle($request);

        if ($language) {
            $_SESSION['_language'] = $language_before;
            restoreLanguage();
        }

        return $response;
    }

    /**
     * Tries to detect a valid installed language from the request.
     *
     * @param Request $request
     * @return string|null The detected language (if any)
     */
    private function detectValidLanguageFromRequest(Request $request): ?string
    {
        if (!$request->hasHeader('Accept-Language')) {
            return null;
        }

        $negotiator = new LanguageNegotiator();
        $best_language = $negotiator->getBest(
            $request->getHeaderLine('Accept-Language'),
            $this->getStudIPLanguagePriorities()
        );

        if (!$best_language) {
            return null;
        }

        return $this->normalizeLanguageForStudIP($best_language->getType());
    }

    /**
     * Returns a list of the normalized installed languages for the Stud.IP
     * system.
     *
     * @return array
     */
    private function getStudIPLanguagePriorities(): array
    {
        return array_map(
            function ($language) {
                return str_replace('_', '-', $language);
            },
            array_keys($GLOBALS['INSTALLED_LANGUAGES'])
        );
    }

    /**
     * Normalizes the given language string (<language>-<variety>, e.g. de-de)
     * for Stud.IP (e.g. de_DE).
     *
     * @param string $language
     * @return string
     */
    private function normalizeLanguageForStudIP(string $language): string
    {
        $tags = explode('-', $language);
        return $tags[0] . '_' . strtoupper($tags[1]);
    }
}
