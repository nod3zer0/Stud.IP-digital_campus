<?php

namespace JsonApi\Routes\Wiki;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\InternalServerError;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\WikiPage;


/**
 * Create a news where the range is the user himself.
 */
class WikiCreate extends JsonApiController
{
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $json = $this->validate($request);

        // TODO: has to be Course or Institute
        $range = \RangeFactory::find($args['id']);
        if (!$range || $range instanceof \User) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);
        if (!Authority::canCreateWiki($user, $range)) {
            throw new AuthorizationFailedException();
        }

        $name = self::arrayGet($json, 'data.attributes.name') ?? self::arrayGet($json, 'data.attributes.keyword');

        if (\WikiPage::findOneBySQL('`range_id` = ? AND `name` = ?', [$range->id, $name])) {
            throw new ConflictException('Wiki page already exists.');
        }

        if (!$wiki = $this->createWikiFromJSON($user, $range, $json)) {
            throw new InternalServerError('Could not create the wiki page.');
        }

        return $this->getCreatedResponse($wiki);
    }

    protected function createWikiFromJSON(\User $user, $range, $json)
    {
        $name    = self::arrayGet($json, 'data.attributes.name') ?? self::arrayGet($json, 'data.attributes.keyword');
        $content = self::arrayGet($json, 'data.attributes.content');
        $content = \Studip\Markup::purifyHtml($content);

        $wiki = new \WikiPage();
        $wiki->name = $name;
        $wiki->content = $content;
        $wiki->chdate = time();
        $wiki->user_id = $user->id;
        $wiki->range_id = $range->id;
        $wiki->store();

        return $wiki;
    }

    protected function validateResourceDocument($json, $data)
    {
        $name = self::arrayGet($json, 'data.attributes.name', '');
        if (empty($name)) {
            return 'Wikis must have a title (keyword)';
        }

        if (!preg_match(WikiPage::REGEXP_KEYWORD, $name)) {
            return 'Malformed wiki keyword.';
        }
    }
}
