<?php
namespace RESTAPI\Routes;

/**
 * @author     <mlunzena@uos.de>
 * @license    GPL 2 or later
 * @deprecated Since Stud.IP 5.0. Will be removed in Stud.IP 6.0.
 *
 * @condition range_id ^[0-9a-f]{1,32}$
 */
class Wiki extends \RESTAPI\RouteMap
{
    public function before()
    {
        require_once 'User.php';
    }

    /**
     * Wikiseitenindex einer Veranstaltung
     *
     * @get /course/:range_id/wiki
     */
    public function getCourseWiki($range_id)
    {
        $pages = \WikiPage::findBySQL("`range_id` = ? ORDER BY `name` ASC", [$range_id]);

        if (!$pages[0]->isReadable()) {
            $this->error(401);
        }

        $total = sizeof($pages);
        $pages = $pages->limit($this->offset, $this->limit);

        $linked_pages = [];
        foreach ($pages as $page) {
            $url = $this->urlf('/course/%s/wiki/%s', [$range_id, htmlReady($page['keyword'])]);
            $linked_pages[$url] = $this->wikiPageToJson($page, ["content"]);
        }

        $this->etag(md5(serialize($linked_pages)));

        return $this->paginated($linked_pages, $total, compact('range_id'));
    }

    /**
     * Wikiseite auslesen
     *
     * @get /course/:range_id/wiki/:keyword
     * @get /course/:range_id/wiki/:keyword/:version
     */
    public function getCourseWikiKeyword($range_id, $keyword, $version = null)
    {
        $page = $this->requirePage($range_id, $keyword, $version);
        $wiki_json = $this->wikiPageToJson($page);
        $this->etag(md5(serialize($wiki_json)));
        $this->lastmodified($page->chdate);
        return $wiki_json;
    }

    /**
     * Wikiseite ändern/hinzufügen
     *
     * @put /course/:range_id/wiki/:keyword
     */
    public function putCourseWikiKeyword($range_id, $keyword)
    {
        if (!isset($this->data['content'])) {
            $this->error(400, 'No content provided');
        }

        $page =\WikiPage::findOneBySQL("`range_id` = ? AND `name` = ?", [$range_id, $keyword]);
        if (!$page) {
            $page = new \WikiPage();
            $page->range_id = $range_id;
            $page->name = $keyword;
        }

        if (!$page->isEditable()) {
            $this->error(401);
        }

        $page->content = $this->data['content'];
        $page->store();

        $url = sprintf('course/%s/wiki/%s/%d', htmlReady($range_id), htmlReady($keyword), count($page->versions) + 1);
        $this->redirect($url, 201, 'ok');
    }

    /**************************************************/
    /* PRIVATE HELPER METHODS                         */
    /**************************************************/

    private function requirePage($range_id, $keyword, $version = null)
    {
        $page = \WikiPage::findOneBySQL("`range_id` = ? AND `name` = ?", [$range_id, $keyword]);

        if (!$page) {
            $this->notFound();
        }

        if (!$page->isReadable($GLOBALS['user']->id)) {
            $this->error(401);
        }
        if ($version !== null && $version !== count($page->versions) + 1) {
            return $page->versions[count($page->versions) - 1 - $version];
        } else {
            return $page;
        }
    }

    private function wikiPageToJson($page, $without = [])
    {
        $json = [
            'range_id' => $page->range_id,
            'keyword'  => $page->name,
            'chdate'   => $page->chdate,
            'version'  => 1
        ];

        // (pre-rendered) content
        if (!in_array('content', $without)) {
            $json['content']      = $page->content;
            $json['content_html'] = wikiReady($page->content, true, $page->range_id, $page->id);
        }
        if (!in_array('user', $without)) {
            if ($page->author) {
                $json['user'] = User::getMiniUser($this, $page->user_id);
            }
        }

        foreach ($without as $key) {
            if (isset($json[$key])) {
                unset($json[$key]);
            }
        }

        // string to int conversions as SORM does not know about ints
        foreach (['chdate', 'mkdate', 'filesize', 'downloads'] as $key) {
            if (isset($json[$key])) {
                $json[$key] = (int) $json[$key];
            }
        }

        return $json;
    }


}
