<?php

use JsonApi\Routes\Events\UserEventsIcal;

class UserEventsIcalTest extends \Codeception\Test\Unit
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
    public function testIcalUserEvents()
    {
        $credentials = $this->tester->getCredentialsForTestAutor();

        $event = new \CalendarDate();
        $event->setId($event->getNewId());
        $now = time();
        $event->begin = $now;
        $event->end = $now + 3600;
        $event->title = 'blypyp';
        $event->store();
        $calendar_date = new \CalendarDateAssignment();
        $calendar_date->setId([$credentials['id'], $event->getId()]);
        $calendar_date->calendar_date = $event;
        $calendar_date->suppress_mails = true;
        $calendar_date->store();

        $app = $this->tester->createApp($credentials, 'get', '/users/{id}/events.ics', UserEventsIcal::class);

        $requestBuilder = $this->tester->createRequestBuilder($credentials);
        $requestBuilder->setUri('/users/'.$credentials['id'].'/events.ics')->fetch();

        $response = $app->handle($requestBuilder->getRequest());

        $this->tester->assertEquals(200, $response->getStatusCode());
        $this->tester->assertStringContainsString('BEGIN:VEVENT', (string) $response->getBody());
        $this->tester->assertStringContainsString('SUMMARY:blypyp', (string) $response->getBody());
    }
}
