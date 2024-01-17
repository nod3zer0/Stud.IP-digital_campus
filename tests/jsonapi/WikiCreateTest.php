<?php

use JsonApi\Routes\Wiki\WikiCreate;

class WikiCreateTest extends \Codeception\Test\Unit
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
    public function testWikiCreate()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $rangeId = 'a07535cf2f8a72df33c12ddfa4b53dde';

        $name = 'IphiklosIphitos';
        $content = 'This is just fake wiki.';

        $json = [
            'data' => [
                'type' => 'wiki',
                'attributes' => compact('name', 'content'),
            ],
        ];

        $this->tester->assertCount(0, \WikiPage::findBySQL('`range_id` = ?', [$rangeId]));

        $response = $this->createWikiPage($credentials, $rangeId, $json);
        $this->tester->assertSame(201, $response->getStatusCode());

        $this->tester->assertCount(1, \WikiPage::findBySQL('`range_id` = ?', [$rangeId]));

        $page = $response->document()->primaryResource();

        $this->tester->assertEquals($content, $page->attribute('content'));
    }

    //helpers:
    private function createWikiPage($credentials, $rangeId, $json)
    {
        $app = $this->tester->createApp(
            $credentials,
            'post',
            '/courses/{id}/wiki',
            WikiCreate::class
        );
        $app->get('/wiki-pages/{id}', function () {})->setName('get-wiki-page');

        return $this->tester->sendMockRequest(
            $app,
            $this->tester->createRequestBuilder($credentials)
            ->setUri('/courses/'.$rangeId.'/wiki')
            ->setJsonApiBody($json)
            ->create()
            ->getRequest()
        );
    }
}
