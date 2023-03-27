<?php
/*
 * request_test.php - unit tests for the Request class
 *
 * Copyright (c) 2009  Elmar Ludwig
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

/**
 * @backupGlobals enabled
 */
class RequestTest extends \Codeception\Test\Unit
{
    public function setUp(): void
    {
        unset($_SERVER['HTTPS']);
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['SCRIPT_NAME']);
        unset($_SERVER['SERVER_NAME']);
        unset($_SERVER['SERVER_PORT']);
    }

    protected function setScriptName(string $script_name): void
    {
        $_SERVER['SCRIPT_NAME'] = $script_name;
    }

    protected function setRequestUri(string $request_uri): void
    {
        $_SERVER['REQUEST_URI'] = $request_uri;
    }

    protected function setServerNameAndPort(string $name, int $port, bool $ssl = false): void
    {
        $_SERVER['SERVER_NAME'] = $name;
        $_SERVER['SERVER_PORT'] = $port;
        $_SERVER['HTTPS'] = $ssl ? 'on' : 'off';
    }

    /**
     * @covers Request::protocol
     */
    public function testProtocol(): void
    {
        $this->assertEquals('http', Request::protocol());

        $_SERVER['HTTPS'] = 'on';
        $this->assertEquals('https', Request::protocol());

        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'foo';
        $this->assertEquals('foo', Request::protocol());
    }

    /**
     * @covers Request::server
     */
    public function testServer(): void
    {
        // Usual ports for http and https
        $this->setServerNameAndPort('www.studip.de', 80);
        $this->assertEquals('www.studip.de', Request::server());

        $this->setServerNameAndPort('www.studip.de', 443, true);
        $this->assertEquals('www.studip.de', Request::server());

        // Unusual ports for http and https
        $this->setServerNameAndPort('www.studip.de', 80, true);
        $this->assertEquals('www.studip.de:80', Request::server());

        $this->setServerNameAndPort('www.studip.de', 443, false);
        $this->assertEquals('www.studip.de:443', Request::server());

        // Other tests
        $this->setServerNameAndPort('www.studip.de', 8088);
        $this->assertEquals('www.studip.de:8088', Request::server());

        $this->setServerNameAndPort('www.studip.de', 8088, true);
        $this->assertEquals('www.studip.de:8088', Request::server());
    }

    /**
     * @covers Request::path()
     */
    public function testPath(): void
    {
        $this->setRequestUri('/foo');
        $this->assertEquals('/foo', Request::path());
    }

    /**
     * @depends testProtocol
     * @depends testServer
     * @depends testPath
     * @covers Request::url
     */
    public function testURL(): void
    {
        $this->setServerNameAndPort('www.example.com', 443, true);
        $this->setRequestUri('/do/it?now=1');
        $this->assertEquals('https://www.example.com/do/it?now=1', Request::url());

        $this->setServerNameAndPort('www.example.com', 8080);
        $this->setRequestUri('/index.php');
        $this->assertEquals('http://www.example.com:8080/index.php', Request::url());
    }

    public function testScriptName(): void
    {
        $this->setScriptName('/index.php');
        $this->assertEquals('/index.php', Request::scriptName());
    }

    /**
     * @depends testPath
     * @depends testScriptName
     * @covers       Request::pathInfo
     * @dataProvider PathProvider
     */
    public function testPathInfo(string $request_uri, string $script_name, string $expected): void
    {
        $this->setScriptName($script_name);
        $this->setRequestUri($request_uri);
        $this->assertEquals($expected, Request::pathInfo());
    }

    /**
     * Data provider for testGetCompletePathInfo
     *
     * @return array[]
     * @see RequestTest::testPathInfo
     */
    public function PathProvider(): array
    {
        return [
            'Regular'                => ['/studip/dispatch.php/start', '/studip/dispatch.php', '/start'],
            'With duplicate slash'   => ['/plugins.php/foo/bar//42', '/plugins.php', '/foo/bar//42'],
            'With duplicate slashes' => ['/bogus.php/1/2//4///7///', '/bogus.php', '/1/2//4///7///'],
            'Encoded'                => ['/dispatch.php/%62lu%62%62er', '/dispatch.php', '/blubber'],
        ];
    }
}
