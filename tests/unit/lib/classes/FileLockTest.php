<?php
/*
 * Copyright (c) 2022  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class FileLockTest extends \Codeception\Test\Unit
{
    public function testAquireLock()
    {
        $lock = new FileLock('test');
        $lock2 = new FileLock('test');

        $this->assertTrue($lock->tryLock());
        $lock->release();

        $this->assertTrue($lock2->tryLock());
        $lock2->release();
    }

    public function testBusyLock()
    {
        $lock = new FileLock('test');
        $lock2 = new FileLock('test');

        $data = ['foo' => '42'];
        $this->assertTrue($lock->tryLock($data));

        // test updating a lock
        $data = ['foo' => 'bar'];
        $this->assertTrue($lock->tryLock($data));

        // aquiring the lock should fail
        $data = [];
        $this->assertFalse($lock2->tryLock($data));
        $this->assertEquals('bar', $data['foo']);

        $lock->release();
    }
}
