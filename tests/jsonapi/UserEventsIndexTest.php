<?php

use JsonApi\Routes\Events\UserEventsIndex;

class UserEventsIndexTest extends \Codeception\Test\Unit
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

    // tests
    public function testIndexUserEvents()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();
        $response = $this->getEvents($credentials);
        $this->assertSuccessfulResponse($response);

        $response = $this->getEvents($credentials);
        $this->assertSuccessfulResponse($response);
        $numEvents = count($response->document()->primaryResources());

        $this->createEvent($credentials);

        $response = $this->getEvents($credentials);
        $this->assertSuccessfulResponse($response);
        $this->tester->assertCount($numEvents + 1, $response->document()->primaryResources());
    }

    private function getEvents($credentials)
    {
        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/events', UserEventsIndex::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/users/' . $credentials['id'] . '/events')->fetch();

        return $this->tester->sendMockRequest($app, $requestBuilder->getRequest());
    }

    private function assertSuccessfulResponse($response)
    {
        $this->tester->assertTrue($response->isSuccessfulDocument([200]));
        $document = $response->document();
        $this->tester->assertTrue($document->isResourceCollectionDocument());
    }

    private function createEvent($credentials)
    {
        $event = new \CalendarDate();
        $event->setId($event->getNewId());
        $now = time();
        $event->begin = $now;
        $event->end = $now + 3600;
        $event->store();
        $calendar_date = new \CalendarDateAssignment();
        $calendar_date->setId([$credentials['id'], $event->getId()]);
        $calendar_date->calendar_date = $event;
        $calendar_date->suppress_mails = true;
        $calendar_date->store();
    }
}
