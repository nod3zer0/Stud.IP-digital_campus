<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;

class WikiDelete extends JsonApiController
{
    use HelperTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $wikiPage = self::findWikiPage($args['id']);

        if (!Authority::canDeleteWiki($this->getUser($request), $wikiPage)) {
            throw new AuthorizationFailedException();
        }

        $wikiPage->delete();

        return $this->getCodeResponse(204);
    }
}
