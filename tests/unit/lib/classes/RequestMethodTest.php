<?php
/**
 * @backupGlobals enabled
 */
class RequestMethodTest extends \Codeception\Test\Unit
{
    public function setUp(): void
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    protected function setRequestMethod($method)
    {
        $_SERVER['REQUEST_METHOD'] = (string)$method;
    }

    public function testMethod()
    {
        $this->setRequestMethod('GET');
        $this->assertEquals('GET', Request::method());
    }

    public function testMethodUppercases()
    {
        $this->setRequestMethod('gEt');
        $this->assertEquals('GET', Request::method());
    }

    public function testRequestMethodGet()
    {
        $this->setRequestMethod('GET');
        $this->assertTrue(Request::isGet());
    }

    public function testRequestMethodPost()
    {
        $this->setRequestMethod('POST');
        $this->assertTrue(Request::isPost());
    }

    public function testRequestMethodPut()
    {
        $this->setRequestMethod('PUT');
        $this->assertTrue(Request::isPut());
    }

    public function testRequestMethodDelete()
    {
        $this->setRequestMethod('DELETE');
        $this->assertTrue(Request::isDelete());
    }

    public function testIsNotXhr()
    {
        $this->assertFalse(Request::isXhr());
        $this->assertFalse(Request::isAjax());
    }

    public function testIsXhr()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XmlHttpRequest';
        $this->assertTrue(Request::isAjax());
        $this->assertTrue(Request::isXhr());
    }
}
