<?php
/*
 * SimpleOrMapNodbTest - unit tests for the SimpleOrMap class without database access
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class auth_user_md5 extends SimpleORMap
{
    public $additional_dummy_data = null;

    protected static function configure($config = [])
    {
        $config['db_table'] = 'auth_user_md5';
        $config['additional_fields']['additional']['get'] = function ($record, $field) {return $record->additional_dummy_data;};
        $config['additional_fields']['additional']['set'] = function ($record, $field, $data) {return $record->additional_dummy_data = $data;};
        $config['serialized_fields']['csvdata'] = 'CSVArrayObject';
        $config['serialized_fields']['jsondata'] = 'JSONArrayObject';
        $config['notification_map']['after_store'] = 'auth_user_md5DidCreateOrUpdate';

        $config['i18n_fields'] = ['i18n_field'];

        parent::configure($config);
    }

    function getPerms()
    {
        return 'ok:' . $this->content['perms'];
    }

    function setPerms($perm)
    {
        return $this->content['perms'] = mb_strtolower($perm);
    }

    public static function registerCallback($types, $cb)
    {
        return parent::registerCallback($types, $cb);
    }
}

class SimpleOrMapNodbTest extends \Codeception\Test\Unit
{
    protected static function setupFixture(): void
    {
        StudipTestHelper::set_up_tables(['auth_user_md5']);
    }

    protected static function teardownFixture(): void
    {
        StudipTestHelper::tear_down_tables();
    }

    public function setUp(): void
    {
        self::setupFixture();
    }

    public function tearDown(): void
    {
        self::teardownFixture();
    }

    /**
     * @covers SimpleORMap::__construct
     */
    public function testConstruct(): auth_user_md5
    {
        $a = new auth_user_md5();
        $this->assertInstanceOf(SimpleORMap::class, $a);
        return $a;
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::getTableMetadata
     */
    public function testMetaData($a)
    {
        $meta = $a->getTableMetadata();
        //$this->assertEquals('auth_user_md5', $meta['db_table']);
        $this->assertEquals('user_id', $meta['pk'][0]);
        $this->assertArrayHasKey('email', $meta['fields']);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::getDefaultValue
     */
    public function testDefaults($a)
    {
        $this->assertEquals(null, $a->email);
        $this->assertEquals('unknown', $a->visible);
        $this->assertEquals('', $a->validation_key);
        $this->assertInstanceOf(CSVArrayObject::class, $a->csvdata);
        $this->assertEquals('1,3', (string)$a->csvdata);
        $this->assertInstanceOf(JSONArrayObject::class, $a->jsondata);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::getValue
     * @covers SimpleORMap::setValue
     */
    public function testGetterAndSetter($a)
    {
        $mail = 'noack@data-quest';
        $a->email = $mail;
        $this->assertEquals($mail, $a->email);
        $this->assertEquals($mail, $a->EMAIL);
        $mail = 'anoack@data-quest';
        $a['email'] = $mail;
        $this->assertEquals($mail, $a['email']);
        $a->perms = 'ADMIN';
        $this->assertEquals('ok:admin', $a['perms']);
        $a->csvdata = '1,2,3,4,5';
        $this->assertInstanceOf(CSVArrayObject::class, $a->csvdata);
        $this->assertEquals('1,2,3,4,5', (string)$a->csvdata);
        $this->assertEquals(range(1,5), $a['csvdata']->getArrayCopy());
        $a->jsondata = [0 => 'test1', 1 => 'test2'];
        $this->assertInstanceOf(JSONArrayObject::class, $a->jsondata);
        $this->assertEquals('["test1","test2"]', (string)$a->jsondata);
        $a->jsondata[] = [1,2,3];
        $this->assertInstanceOf(JSONArrayObject::class, $a->jsondata[2]);
        $this->assertEquals('["test1","test2",[1,2,3]]', (string)$a->jsondata);
        $a->jsondata[2][] = ['test3' => 'test3'];
        $this->assertEquals('["test1","test2",[1,2,3,{"test3":"test3"}]]', (string)$a->jsondata);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::isDirty
     * @covers SimpleORMap::isFieldDirty
     */
    public function testDirty($a)
    {
        $this->assertEquals(true, $a->isDirty());
        $this->assertEquals(true, $a->isFieldDirty('email'));
        $this->assertEquals(false, $a->isFieldDirty('vorname'));
        $this->assertEquals(true, $a->isFieldDirty('csvdata'));
        $this->assertEquals(true, $a->isFieldDirty('jsondata'));
        $a->csvdata[1] = '3';
        unset($a->csvdata[2]);
        unset($a->csvdata[3]);
        unset($a->csvdata[4]);
        $this->assertEquals(false, $a->isFieldDirty('csvdata'));
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::revertValue
     */
    public function testRevert($a)
    {
        $a->revertValue('email');
        $a->revertValue('perms');
        $a->revertValue('csvdata');
        $a->revertValue('jsondata');
        $this->assertEquals(false, $a->isDirty());
        $this->assertEquals(false, $a->isFieldDirty('email'));
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::setData
     */
    public function testsetData($a)
    {
        $a->vorname = 'André';
        $data['email'] = 'fuhse@data-quest.de';
        $data['vorname'] = 'Rasmus';
        $data['nachname'] = 'Fuhse';
        $data['USERNAME'] = 'krassmus';
        $data['csvdata'] = range(1,4);
        $data['jsondata'] = [0 => [0 => [0 => 1]]];
        $a->setData($data, true);
        $this->assertEquals($data['vorname'], $a->vorname);
        $this->assertEquals($data['nachname'], $a->nachname);
        $this->assertEquals($data['email'], $a->email);
        $this->assertEquals($data['USERNAME'], $a->username);
        $this->assertEquals('1,2,3,4', (string)$a->csvdata);
        $this->assertEquals('[[[1]]]', (string)$a->jsondata);
        $this->assertEquals(false, $a->isDirty());

        $data2['vorname'] = 'Krassmus';
        $data2['username'] = 'rasmus';
        $a->setData($data2, false);
        $this->assertEquals($data2['vorname'], $a->vorname);
        $this->assertEquals($data2['username'], $a->username);
        $this->assertEquals($data['nachname'], $a->nachname);
        $this->assertEquals($data['email'], $a->email);
        $this->assertEquals(true, $a->isDirty());
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::getId()
     * @covers SimpleORMap::setId()
     */
    public function testPrimaryKey($a)
    {
        $a->setId(1);
        $this->assertEquals(1, $a->user_id);
        $this->assertEquals(1, $a->id);
        $this->assertEquals(1, $a->getId());
        $a->id = 2;
        $this->assertEquals(2, $a->user_id);
        $this->assertEquals(2, $a->id);
        $this->assertEquals(2, $a->getId());
        $a->revertValue('id');
        $this->assertNull($a->id);
        $a->user_id = 2;
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::isAdditionalField
     * @covers SimpleORMap::_setAdditionalValue
     * @covers SimpleORMap::_getAdditionalValue
     */
    public function testAdditional($a)
    {
        $this->assertTrue($a->isAdditionalField('additional'));
        $this->assertNull($a->additional);
        $a->additional = 'test';
        $this->assertEquals($a->additional_dummy_data, $a->additional);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::toArray
     */
    public function testToArray($a)
    {
        $to_array = $a->toArray();
        $this->assertEquals(2, $to_array['id']);
        $this->assertEquals(2, $to_array['user_id']);
        $this->assertEquals('test', $to_array['additional']);
        $this->assertEquals('ok:user', $to_array['perms']);
        $this->assertEquals(range(1,4), $to_array['csvdata']);
        $this->assertArrayHasKey('visible', $to_array);
        $this->assertCount(18, $to_array);

        $to_array = $a->toArray('id user_id additional perms');
        $this->assertEquals(2, $to_array['id']);
        $this->assertEquals(2, $to_array['user_id']);
        $this->assertEquals('test', $to_array['additional']);
        $this->assertEquals('ok:user', $to_array['perms']);
        $this->assertArrayNotHasKey('visible', $to_array);
        $this->assertCount(4, $to_array);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::setValue
     */
    public function testInvalidColumnException($a)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown not found.');
        $a->unknown = 1;
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::registerCallback
     */
    public function testCallback($a)
    {
        $callback_was_here = null;
        $cb = function ($record, $type) use (&$callback_was_here)
        {
            $callback_was_here = $type;
            $record->id = 3;
            return false;
        };
        $a->registerCallback('before_store', $cb);
        $stored = $a->store();
        $this->assertFalse($stored);
        $this->assertEquals(3, $a->id);
        $this->assertEquals('before_store', $callback_was_here);
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::applyCallbacks
     */
    public function testNotification($a)
    {
        $callback_was_here = null;
        $cb = function ($type, $record) use (&$callback_was_here)
        {
            $callback_was_here = $type;
            $record->id = 3;
            throw new NotificationVetoException('veto');
        };
        NotificationCenter::addObserver($cb, '__invoke', 'auth_user_md5WillStore', $a);
        $stored = $a->store();
        $this->assertFalse($stored);
        $this->assertEquals(3, $a->id);
        $this->assertEquals('auth_user_md5WillStore', $callback_was_here);
    }


    /**
     * @depends testConstruct
     */
    public function testSerialization($a)
    {
        $serialized = serialize($a);
        $this->assertIsString($serialized);

        $unserialized = unserialize($serialized);

        $this->assertEquals($a->toArray(), $unserialized->toArray());
    }

    /**
     * @depends testConstruct
     * @covers SimpleORMap::__clone
     */
    public function testClone(auth_user_md5 $a)
    {
        $queue = new SplStack();
        $queue->push(1);
        $queue->push(2);
        $queue->push(3);

        $a->additional_dummy_data = $queue;

        $b = clone $a;

        $this->assertEquals(
            $a->additional_dummy_data->count(),
            $b->additional_dummy_data->count()
        );
    }

    /**
     * @dataProvider  i18nProvider
     * @covers SimpleORMap::isI18nField
     * @covers SimpleORMap::i18n_fields
     */
    public function testI18nFields(SimpleORMap $a): void
    {
        $this->assertTrue($a->isI18nField('i18n_field'));
        $this->assertInstanceOf(I18NString::class, $a->i18n_field);
    }

    public static function i18nProvider(): array
    {
        self::setupFixture();

        $result = [
            'definition as list' => [new auth_user_md5()],
            'definition as associative array' => [new class extends SimpleORMap {
                protected static function configure($config = [])
                {
                    $config['db_table'] = 'auth_user_md5';
                    $config['i18n_fields'] = ['i18n_field' => true];
                    parent::configure($config);
                }
            }],
            'definition as string' => [new class extends SimpleORMap {
                protected static function configure($config = [])
                {
                    $config['db_table'] = 'auth_user_md5';
                    $config['i18n_fields'] = 'i18n_field';
                    parent::configure($config);
                }
            }]
        ];

        self::teardownFixture();

        return $result;
    }
}
