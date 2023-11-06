<?php

use JsonApi\Routes\Institutes\InstituteMembershipsIndex;

class InstituteMembershipsIndexTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        \DBManager::getInstance()->setConnection('studip', $this->getModule('\\Helper\\StudipDb')->dbh);
    }

    protected function _after()
    {
    }

    public function testIndexMemberships()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $instituteId = '2560f7c7674942a7dce8eeb238e15d93';

        $institute = \Institute::find($instituteId);

        $app = $this->tester->createApp($credentials, 'get', '/institutes/{id}/memberships', InstituteMembershipsIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/institutes/'.$instituteId.'/memberships')->fetch();

        $response = $this->tester->sendMockRequest($app, $requestBuilder->getRequest());

        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
        $resources = $document->primaryResources();
        $this->tester->assertCount(count($institute->members), $resources);
    }
}
