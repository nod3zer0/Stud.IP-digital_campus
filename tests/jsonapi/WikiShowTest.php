<?php

use JsonApi\Routes\Wiki\WikiShow;

class WikiShowTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
        \WikiPage::deleteBySQL('1');
    }

    protected function _after()
    {
    }

    //testszenario:
    public function testShowWiki()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $keyword = 'NEU';
        $content = 'Es gibt im Moment in diese Mannschaft, oh, einige Spieler vergessenihren Profi was sie sind. Ich lese nicht sehr viele Zeitungen, aberich habe gehört viele Situationen. Erstens: Wir haben nicht offensivgespielt. Es gibt keine deutsche Mannschaft spielt offensiv und dieNamen offensiv wie Bayern. Letzte Spiel hatten wir in Platz dreiSpitzen: Elber, Jancker und dann Zickler. Wir mussen nicht vergessenZickler. Zickler ist eine Spitzen mehr, Mehmet mehr Basler. Ist klardiese Wörter, ist möglich verstehen, was ich hab’ gesagt? Danke.';
        $this->createWikiPage($rangeId, $keyword, $content);

        $this->tester->assertCount(1, \WikiPage::findBySQL('`range_id` = ?', [$rangeId]));

        $response = $this->getWikiPage($credentials, $rangeId, $keyword);
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));

        $page = $response->document()->primaryResource();

        $this->tester->assertEquals($content, $page->attribute('content'));
    }

    //helpers:
    private function getWikiPage($credentials, $rangeId, $keyword)
    {
        $app = $this->tester->createApp($credentials, 'get', '/wiki-pages/{id}', WikiShow::class, 'get-wiki-page');

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/wiki-pages/'.$rangeId.'_'.$keyword)
                ->fetch()
                ->getRequest()
        );
    }

    private function createWikiPage($rangeId, $keyword, $content)
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        // EVIL HACK
        $oldPerm = $GLOBALS['perm'] ?? null;
        $oldUser = $GLOBALS['user'] ?? null;
        $GLOBALS['perm'] = new \Seminar_Perm();
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $wikiPage = new \WikiPage();
        $wikiPage->name = $keyword;
        $wikiPage->range_id = $rangeId;
        $wikiPage->content = $content;
        $wikiPage->user_id = 'nobody';
        $wikiPage->store();

        $GLOBALS['perm'] = $oldPerm;
        $GLOBALS['user'] = $oldUser;

        return $wikiPage;
    }
}
