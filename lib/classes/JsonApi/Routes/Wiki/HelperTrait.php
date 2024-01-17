<?php

namespace JsonApi\Routes\Wiki;

use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;

trait HelperTrait
{
    protected static function findWikiPage($wikiPageId)
    {
        if (is_numeric($wikiPageId)) {
            $page = \WikiPage::find($wikiPageId);
            if (!$page) {
                throw new RecordNotFoundException();
            } else {
                return $page;
            }
        }

        if (!preg_match('/^([^_]+)_(.+)$/', $wikiPageId, $matches)) {
            throw new BadRequestException();
        }

        if (!$wikiPage = \WikiPage::findOneBySQL('`range_id` = ? AND `name` = ?', [$matches[1], $matches[2]])) {
            throw new RecordNotFoundException();
        }

        return $wikiPage;
    }
}
