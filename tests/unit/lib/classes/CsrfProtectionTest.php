<?php
/*
 * csrf_protection_test.php - unit tests for the Request class
 *
 * Copyright (c) 2011 mlunzena
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class CSRFProtectionTokenTest extends \Codeception\Test\Unit
{
    use CsrfProtectionSessionTrait;

    function setUp(): void
    {
        $this->initializeTokenStorage();
    }

    function testTokenGeneration()
    {
        $this->assertEquals(count($this->storage), 0);
        CSRFProtection::token();
        $this->assertEquals(count($this->storage), 1);
    }

    function testTokenIdentity()
    {
        $this->assertEquals(CSRFProtection::token(), CSRFProtection::token());
    }

    function testTokenSessionDifference()
    {
        $token1 = CSRFProtection::token();

        $this->storage = [];

        $token2 = CSRFProtection::token();

        $this->assertNotEquals($token1, $token2);
    }

    function testTokenIsAString()
    {
        $token = CSRFProtection::token();
        $this->assertIsString($token);
    }

    function testTokenTag()
    {
        $token = CSRFProtection::token();
        $this->assertTrue(mb_strpos(CSRFProtection::tokenTag(), $token) !== FALSE);
    }
}

class CSRFRequestTest extends \Codeception\Test\Unit
{
    use CsrfProtectionSessionTrait;

    private $original_state;
    private $token;

    function setUp(): void
    {
        $this->initializeTokenStorage();

        $this->original_state = [$_POST, $_SERVER];

        $_POST = [];
        $this->token = CSRFProtection::token();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = null;
    }

    function tearDown(): void
    {
        [$_POST, $_SERVER] = $this->original_state;
    }

    function testInvalidUnsafeRequest()
    {
        $this->expectException(InvalidSecurityTokenException::class);
        $_SERVER['REQUEST_METHOD'] = 'POST';
        CSRFProtection::verifyUnsafeRequest();
    }

    function testValidUnsafeRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST[CSRFProtection::TOKEN] = $this->token;
        CSRFProtection::verifyUnsafeRequest();
        $this->assertTrue(true);
    }

    function testSafeRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectException(MethodNotAllowedException::class);
        CSRFProtection::verifyUnsafeRequest();
    }

    function testSafeXHR()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        $this->expectException(MethodNotAllowedException::class);
        CSRFProtection::verifyUnsafeRequest();
    }

    function testUnsafeXHRWithoutToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        unset($_POST['security_token']);
        $this->expectException(InvalidSecurityTokenException::class);
        CSRFProtection::verifyUnsafeRequest();
    }

    function testUnsafeXHRWithToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        $_POST['security_token'] = $this->token;
        CSRFProtection::verifyUnsafeRequest();
        $this->assertTrue(true);
    }
}

trait CsrfProtectionSessionTrait
{
    protected $storage = [];

    protected function initializeTokenStorage()
    {
        CSRFProtection::setStorage($this->storage);
    }
}
