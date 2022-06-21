<?php
require_once __DIR__ . '/../../_bootstrap.php';

class ResourceManagerTest extends \Codeception\Test\Unit
{
    protected $db_handle;
    protected $oldUser;

    protected function _before()
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

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $this->oldUser = $GLOBALS['user'];
        $GLOBALS['user'] = new \Seminar_User(
            \User::findByUsername('root@studip')
        );

        //As a final step we create the SORM objects for our test cases:

        $this->test_cat = new ResourceCategory();
        $this->test_cat->class_name = 'Resource';
        $this->test_cat->store();

        $this->position_def = new ResourcePropertyDefinition();
        $this->position_def->name = 'test_pos';
        $this->position_def->type = 'position';
        $this->position_def->store();

        $this->perm_user = new User();
        $this->perm_user->username = 'test_resource_perm';
        $this->perm_user->perms = 'autor';
        $this->perm_user->store();

        $this->perm_resource = $this->test_cat->createResource(
            'Permission Test Resource'
        );
        $this->perm_resource->store();

        $this->test_def = new ResourcePropertyDefinition();
        $this->test_def->name = 'test_is_test';
        $this->test_def->type = 'bool';
        $this->test_def->store();

        //Everything is set up for the test cases.
    }

    protected function _after()
    {
        //We must roll back the changes we made in this test
        //so that the live database remains unchanged after
        //all the test cases of this test have been finished:
        $this->db_handle->rollBack();

        // Workaround old-style Stud.IP-API using $GLOBALS['user']
        $GLOBALS['user'] = $this->oldUser;
     }

    public function testCreateCategory()
    {
        $category = ResourceManager::createCategory(
            'Test',
            'Test for createCategory',
            'Resource',
            true,
            '9',
            [
                [
                    'test_is_test',
                    true,
                    false,
                    true
                ]
            ]
        );

        $this->assertEquals(
            'Test',
            $category->name
        );

        $this->assertEquals(
            'Test for createCategory',
            $category->description
        );

        $this->assertEquals(
            'Resource',
            $category->class_name
        );

        $this->assertEquals(
            '1',
            $category->system
        );

        $this->assertEquals(
            '9',
            $category->iconnr
        );

        $this->assertEquals(
            true,
            $category->hasProperty('test_is_test', 'bool')
        );
    }

    public function testCreateLocationCategory()
    {
        $location_cat = ResourceManager::createLocationCategory(
            'TestLocation',
            'A test location category',
            [
                [
                    'test_is_test',
                    true,
                    true,
                    true
                ]
            ]
        );

        //Test default properties:
        $this->assertEquals(
            true,
            $location_cat->hasProperty('geo_coordinates', 'position')
        );

        //Test optional properties:
        $this->assertEquals(
            true,
            $location_cat->hasProperty('test_is_test', 'bool')
        );
    }

    public function testCreateBuildingCategory()
    {
        $building_cat = ResourceManager::createBuildingCategory(
            'TestBuilding',
            'A test building category',
            [
                [
                    'test_is_test',
                    true,
                    true,
                    true
                ]
            ]
        );

        //Test default properties:
        $this->assertEquals(
            true,
            $building_cat->hasProperty('address', 'text')
        );

        $this->assertEquals(
            true,
            $building_cat->hasProperty('accessible', 'bool')
        );

        $this->assertEquals(
            true,
            $building_cat->hasProperty('geo_coordinates', 'position')
        );

        $this->assertEquals(
            true,
            $building_cat->hasProperty('number', 'text')
        );

        //Test optional properties:
        $this->assertEquals(
            true,
            $building_cat->hasProperty('test_is_test', 'bool')
        );
    }

    public function testCreateRoomCategory()
    {
        $room_cat = ResourceManager::createRoomCategory(
            'TestRoom',
            'A test room category',
            [
                [
                    'test_is_test',
                    true,
                    true,
                    true
                ]
            ]
        );

        //Test default properties:
        $this->assertEquals(
            true,
            $room_cat->hasProperty('room_type', 'select')
        );

        $this->assertEquals(
            true,
            $room_cat->hasProperty('seats', 'num')
        );

        $this->assertEquals(
            true,
            $room_cat->hasProperty('booking_plan_is_public', 'bool')
        );

        //Test optional properties:
        $this->assertEquals(
            true,
            $room_cat->hasProperty('test_is_test', 'bool')
        );
    }

    public function testCopyResource()
    {
        $copy = ResourceManager::copyResource(
            $this->perm_resource,
            false
        );

        $this->assertEquals(
            $this->perm_resource->name,
            $copy->name
        );
    }

    public function testMoveResource()
    {
        $parent_res = $this->test_cat->createResource('Parent Resource');
        $new_res = $this->test_cat->createResource('Moved Resource');

        $result = ResourceManager::moveResource($new_res, $parent_res);

        $this->assertEquals(true, $result);

        $this->assertEquals(
            $parent_res->id,
            $new_res->parent_id
        );

        $this->assertEquals(
            $parent_res->children[0]->id,
            $new_res->id
        );
    }

    public function testGoodPositions()
    {
        $position = new ResourceProperty();
        $position->getId();
        $position->property_id = $this->position_def->id;
        $position->state = '+8.040295+17.988283+83.934CRSWGS_84/';

        $position_array = ResourceManager::getPositionArray($position);

        $this->assertEquals(
            ['8.040295', '17.988283', '83.934'],
            $position_array
        );

        $position_url = ResourceManager::getMapUrlForResourcePosition($position);

        $this->assertEquals(
            'https://www.openstreetmap.org/#map=19/8.040295/17.988283',
            $position_url
        );

        $position->state = '-14.29302-31.28323-5.292CRSWGS_84/';

        $this->assertEquals(
            ['-14.29302', '-31.28323', '-5.292'],
            ResourceManager::getPositionArray($position)
        );

        $position_url = ResourceManager::getMapUrlForResourcePosition($position);

        $this->assertEquals(
            'https://www.openstreetmap.org/#map=19/-14.29302/-31.28323',
            $position_url
        );
    }

    /**
     * @dataProvider coordinateProvider
     */
    public function testCoordinatesValidation($coordinates)
    {
        $this->assertFalse(ResourceManager::validateCoordinates($coordinates));
    }

    public static function coordinateProvider(): array
    {

        return [
            'empty' => [''],

            'bad-latitude-1' => ['14.29302-31.28323-5.292CRSWGS_84/'],
            'bad-latitude-2' => ['+14-31.28323-5.292CRSWGS_84/'],
            'missing-latitude' => ['-31.28323-5.292CRSWGS_84/'],

            'bad-longitude-1' => ['-14.29302-31-5.292CRSWGS_84/'],
            'bad-longitude-2' => ['-14.29302-+31.28323-5.292CRSWGS_84/'],

            'missing-altitude' => ['-14.29302-31.28323CRSWGS_84/'],

            'bad-suffix' => ['-14.29302-31.28323-5.292CRSWGS_84'],
            'missing-suffix' => ['-14.29302-31.28323-5.292'],
        ];
    }
}
