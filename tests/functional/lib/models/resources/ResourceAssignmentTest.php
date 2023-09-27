<?php


/**
 * ResourceBookingTest.php - A test for the resource booking functionality.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2020
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     tests
 * @since       4.5
 */

class ResourceAssignmentTest extends \Codeception\Test\Unit
{
    protected $db_handle;
    protected $oldPerm;
    protected $oldUser;

    private $test_user_username;
    private $test_user;
    private $resource_category;
    private $resource;
    private $booking;

    protected function setUp(): void
    {
        //First we must initialise the StudipPDO database connection:
        $this->db_handle = new \StudipPDO(
            'mysql:host='
                . $GLOBALS['DB_STUDIP_HOST']
                . ';dbname='
                . $GLOBALS['DB_STUDIP_DATABASE'],
            $GLOBALS['DB_STUDIP_USER'],
            $GLOBALS['DB_STUDIP_PASSWORD']
        );

        //Then we must start a transaction before we access the database,
        //otherwise we would spam the live database with test data!
        $this->db_handle->beginTransaction();

        //Now we tell the DBManager about the connection
        //we have established to the Stud.IP database:
        \DBManager::getInstance()->setConnection('studip', $this->db_handle);

        \Config::get()->setValue('LOG_ENABLE', false);

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $this->oldUser = $GLOBALS['user'] ?? null;
        $GLOBALS['user'] = new \Seminar_User(
            \User::findByUsername('root@studip')
        );
        $this->oldPerm = $GLOBALS['perm'] ?? null;
        $GLOBALS['perm'] = new \Seminar_Perm();

        //As a final step we create the SORM objects for our test cases:

        $this->test_user_username = 'test_user_' . date('YmdHis');
        $this->test_user = User::create([
            'username' => $this->test_user_username,
            'vorname'  => 'Test',
            'nachname' => 'User',
            'perms'    => 'admin',
        ]);

        $perm = new ResourcePermission();
        $perm->user_id = $this->test_user->id;
        $perm->resource_id = 'global';
        $perm->perms = 'admin';
        $perm->store();

        $this->resource_category = ResourceManager::createCategory(
            'Test'
        );
        $this->resource = $this->resource_category->createResource(
            'Test Resource' . date('Ymd')
        );

        $this->booking = $this->resource->createBooking(
            $this->test_user,
            $this->test_user->id,
            [
                [
                    'begin' => new DateTime('2017-12-01 8:00:00+0000'),
                    'end' => new DateTime('2017-12-01 14:00:00+0000')
                ]
            ],
            new DateInterval('P1D'),
            0,
            new DateTime('2017-12-04 15:00:00+0000')
        );
    }

    protected function tearDown(): void
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['perm'] = $this->oldPerm;
        $GLOBALS['user'] = $this->oldUser;
    }

    public function testEndWithSemester()
    {
        $this->assertFalse(
            $this->booking->endsWithSemester()
        );
    }

    public function testIsRepetitionInTimeframe()
    {
        $begin = new DateTime('2017-12-02 0:00:00+0000');
        $end = new DateTime('2017-12-02 23:59:59+0000');

        $this->assertTrue(
            $this->booking->isRepetitionInTimeframe($begin, $end)
        );
    }

    public function testGetRepetitionInterval()
    {
        $interval = new DateInterval('P1D');

        $this->assertEquals(
            $interval,
            $this->booking->getRepetitionInterval()
        );
    }

    public function testGetRepeatModeString()
    {
        $this->assertEquals(
            _('jeden Tag'),
            $this->booking->getRepeatModeString()
        );
    }

    public function testHasOverlappingBookings()
    {
        $this->assertFalse(
            $this->booking->hasOverlappingBookings()
        );
    }

    public function testGetOverlappingBookings()
    {
        $found_overlaps = $this->booking->getOverlappingBookings();

        $count = 0;
        if(!empty($found_overlaps)) {
            $count = count($found_overlaps[0]);
        }

        $this->assertEquals(
            0,
            $count
        );
    }

    public function testGetTimeIntervals()
    {
        $time_intervals = $this->booking->getTimeIntervals();

        $this->assertCount(4, $time_intervals);
    }
}
