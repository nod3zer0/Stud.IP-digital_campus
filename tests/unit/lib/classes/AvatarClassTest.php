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

abstract class AvatarTest extends \Codeception\Test\Unit
{
    protected $avatar_id;
    protected $avatar;

    abstract protected function getType(): string;

    protected function createPath(string $avatar_id, ?string $size, bool $subdir): string
    {
        $result = $avatar_id;

        if ($size) {
            $result .= "_{$size}";
        }

        $result .= '.' . Avatar::EXTENSION;

        if ($subdir) {
            $result = substr($avatar_id, 0, 2) . '/' . $result;
        }
        return "/dynamic/{$this->getType()}/{$result}";
    }

    protected function createFixedPath(?string $size): string
    {
        $result = 'nobody';

        if ($size) {
            $result .= "_{$size}";
        }

        $result .= '.' . Avatar::EXTENSION;

        return "/fixed/images/avatars/{$this->getType()}/{$result}";
    }

    protected function setUp(): void
    {
        $GLOBALS['DYNAMIC_CONTENT_URL'] = "/dynamic";
        $GLOBALS['DYNAMIC_CONTENT_PATH'] = "/dynamic";
        $GLOBALS['ASSETS_URL'] = "/fixed/";
        $GLOBALS['ASSETS_PATH'] = "/fixed/";

        Assets::set_assets_url($GLOBALS['ASSETS_URL']);
        Assets::set_assets_path($GLOBALS['ASSETS_PATH']);
    }

    public function tearDown(): void
    {
        unset(
            $GLOBALS['DYNAMIC_CONTENT_PATH'],
            $GLOBALS['DYNAMIC_CONTENT_URL'],
            $GLOBALS['ASSETS_URL'],
            $GLOBALS['ASSETS_PATH']
        );
    }

    public function test_avatar_url()
    {
        $this->assertEquals(
            $this->createPath($this->avatar_id, Avatar::NORMAL, true) . '?d=0',
            $this->avatar->getCustomAvatarUrl(Avatar::NORMAL)
        );
    }

    public function test_avatar_path()
    {
        $this->assertEquals(
            $this->createPath($this->avatar_id, Avatar::NORMAL, true),
            $this->avatar->getCustomAvatarPath(Avatar::NORMAL)
        );
    }

    public function test_nobody_url()
    {
        $this->assertEquals(
            $this->createFixedPath(Avatar::NORMAL),
            $this->avatar->getNobody()->getURL(Avatar::NORMAL)
        );
    }

    public function test_nobody_path()
    {
        $this->assertEquals(
            $this->createFixedPath(Avatar::NORMAL),
            $this->avatar->getNobody()->getFilename(Avatar::NORMAL)
        );
    }
}

/**
 * Testcase for Avatar class.
 *
 * @package    studip
 * @subpackage test
 *
 * @author    mlunzena
 * @copyright (c) Authors
 */
class AvatarTestCase extends AvatarTest
{
    public function setUp(): void
    {
        parent::setUp();

        $stub = $this->createMock('Seminar_Perm');
        // Configure the stub.
        $stub->expects($this->any())
            ->method('have_perm')
            ->will($this->returnValue(true));

        $GLOBALS['perm'] = $stub;

        $this->avatar_id = "123456789";
        $this->avatar = Avatar::getAvatar($this->avatar_id);
    }

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists(Avatar::class));
    }

    protected function getType(): string
    {
        return 'user';
    }
}


class CourseAvatarTestCase extends AvatarTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->avatar_id = "123456789";
        $this->avatar = CourseAvatar::getAvatar($this->avatar_id);
    }

    public function test_class_should_exist()
    {
        $this->assertTrue(class_exists(CourseAvatar::class));
    }

    protected function getType(): string
    {
        return 'course';
    }
}
