<?php

use JsonApi\Routes\Wiki\WikiUpdate;

class WikiUpdateTest extends \Codeception\Test\Unit
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
    public function testCourseWikiUpdate()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $keyword = 'KaineusKalais';
        $content = 'This is just fake wiki.';
        $createdpage = $this->createWikiPage($rangeId, $keyword, $content);

        $newContent = 'Es gibt im Moment in diese Mannschaft, oh, einige Spieler vergessenihren Profi was sie sind. Ich lese nicht sehr viele Zeitungen, aberich habe gehört viele Situationen. Erstens: Wir haben nicht offensivgespielt. Es gibt keine deutsche Mannschaft spielt offensiv und dieNamen offensiv wie Bayern. Letzte Spiel hatten wir in Platz dreiSpitzen: Elber, Jancker und dann Zickler. Wir mussen nicht vergessenZickler. Zickler ist eine Spitzen mehr, Mehmet mehr Basler. Ist klardiese Wörter, ist möglich verstehen, was ich hab’ gesagt? Danke.';

        $response = $this->updateWiki($credentials, $rangeId, $createdpage->id, $newContent);
        $this->tester->assertSame(200, $response->getStatusCode());
        $page = $response->document()->primaryResource();

        $this->tester->assertEquals($newContent, $page->attribute('content'));
    }

    //helpers:
    private function updateWiki($credentials, $rangeId, $page_id, $content)
    {
        $json = [
            'data' => [
                'type' => 'wiki',
                'id' => $page_id,
                'attributes' => compact('content')
            ],
        ];
        $app = $this->tester->createApp($credentials, 'patch', '/wiki-pages/{id}', WikiUpdate::class);
        $app->get('/wiki-pages/{id}', function () {})->setName('get-wiki-page');

        return $this->tester->sendMockRequest(
                $app,
                $this->tester->createRequestBuilder($credentials)
                ->setUri('/wiki-pages/'.$page_id)
                ->setJsonApiBody($json)
                ->update()
                ->getRequest()
        );
    }

    private function createWikiPage($rangeId, $keyword, $body)
    {
        $credentials = $this->tester->getCredentialsForTestDozent();
        // EVIL HACK
        $oldPerm = $GLOBALS['perm'] ?? null;
        $oldUser = $GLOBALS['user'] ?? null;
        $GLOBALS['perm'] = new \Seminar_Perm();
        $GLOBALS['user'] = new \Seminar_User(\User::find($credentials['id']));

        $wikiPage = new \WikiPage();
        $wikiPage->range_id = $rangeId;
        $wikiPage->name = $keyword;
        $wikiPage->content = $body;
        $wikiPage->user_id = 'nobody';
        $wikiPage->store();

        $GLOBALS['perm'] = $oldPerm;
        $GLOBALS['user'] = $oldUser;

        return $wikiPage;
    }
}
