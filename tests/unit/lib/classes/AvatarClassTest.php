<?php

/*
 * Copyright (C) 2009 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once 'lib/phplib/Seminar_Perm.class.php';

/**
 * Testcase for Avatar class.
 *
 * @package    studip
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */
class AvatarTestCase extends  \Codeception\Test\Unit
{
    private $avatar_id;
    private $avatar;

    public function setUp(): void
    {
        $stub = $this->createMock('Seminar_Perm');
        // Configure the stub.
        $stub->expects($this->any())
            ->method('have_perm')
            ->will($this->returnValue(true));

        $GLOBALS['perm'] = $stub;
        $GLOBALS['DYNAMIC_CONTENT_URL'] = "/dynamic";
        $GLOBALS['DYNAMIC_CONTENT_PATH'] = "/dynamic";
        $this->avatar_id = "123456789";
        $this->avatar = Avatar::getAvatar($this->avatar_id);
    }

    public function tearDown(): void
    {
        unset($GLOBALS['DYNAMIC_CONTENT_PATH'], $GLOBALS['DYNAMIC_CONTENT_URL']);
    }

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('Avatar'));
    }

    public function test_avatar_url()
    {
        $url = $this->avatar->getCustomAvatarUrl(Avatar::NORMAL);
        $this->assertEquals("/dynamic/user/" . $this->avatar_id . "_normal.png?d=0", $url);
    }

    public function test_avatar_path()
    {
        $path = $this->avatar->getCustomAvatarPath(Avatar::NORMAL);
        $this->assertEquals("/dynamic/user/" . $this->avatar_id . "_normal.png", $path);
    }

    public function test_nobody_url()
    {
        $url = Avatar::getNobody()->getUrl(Avatar::NORMAL);
        $this->assertEquals("/dynamic/user/nobody_normal.png?d=0", $url);
    }

    public function test_nobody_path()
    {
        $path = Avatar::getNobody()->getCustomAvatarPath(Avatar::NORMAL);
        $this->assertEquals("/dynamic/user/nobody_normal.png", $path);
    }
}


class CourseAvatarTestCase extends \Codeception\Test\Unit
{
    private $avatar_id;
    private $avatar;

    public function setUp(): void
    {
        $this->avatar_id = "123456789";
        $this->avatar = CourseAvatar::getAvatar($this->avatar_id);

        $this->setUpFS();

        $GLOBALS['DYNAMIC_CONTENT_URL'] = "/dynamic";
        $GLOBALS['DYNAMIC_CONTENT_PATH'] = "/dynamic";
    }

    private function setUpFS()
    {
        ArrayFileStream::set_filesystem([
            'dynamic' => [
                'course' => [
                    $this->avatar_id . '_normal.png' => '',
                    $this->avatar_id . '_medium.png' => '',
                    $this->avatar_id . '_small.png' => '',
                ],
            ],
        ]);

        if (!stream_wrapper_register("var", "ArrayFileStream")) {
            throw new Exception("Failed to register protocol");
        }
    }

    public function tearDown(): void
    {
        stream_wrapper_unregister("var");
        unset($GLOBALS['DYNAMIC_CONTENT_PATH'], $GLOBALS['DYNAMIC_CONTENT_URL']);
    }

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists('CourseAvatar'));
    }

    public function test_avatar_url()
    {
        $url = $this->avatar->getCustomAvatarUrl(Avatar::NORMAL);
        $this->assertEquals("/dynamic/course/". $this->avatar_id . "_normal.png?d=0", $url);
    }

    public function test_avatar_path()
    {
        $path = $this->avatar->getCustomAvatarPath(Avatar::NORMAL);
        $this->assertEquals("/dynamic/course/". $this->avatar_id . "_normal.png", $path);
    }

    public function test_nobody_url()
    {
        $url = CourseAvatar::getNobody()->getUrl(Avatar::NORMAL);
        $this->assertEquals("/dynamic/course/nobody_normal.png?d=0", $url);
    }

    public function test_nobody_path()
    {
        $path = CourseAvatar::getNobody()->getCustomAvatarPath(Avatar::NORMAL);
        $this->assertEquals("/dynamic/course/nobody_normal.png", $path);
    }
}
