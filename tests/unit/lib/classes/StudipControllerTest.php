<?php
/**
 * StudipControllerTest.php - unit tests for the StudipController class
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license  GPL2 or any later version
 *
 * @covers StudipController
 */

final class StudipControllerTest extends Codeception\Test\Unit
{
    private $old_uri;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require_once 'vendor/trails/trails-abridged.php';
        require_once 'app/controllers/studip_controller.php';
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->old_uri = $GLOBALS['ABSOLUTE_URI_STUDIP'] ?? null;
        $GLOBALS['ABSOLUTE_URI_STUDIP'] = 'https://studip.example.org/';
    }

    public function tearDown(): void
    {
        $GLOBALS['ABSOLUTE_URI_STUDIP'] = $this->old_uri;

        parent::tearDown();
    }

    private function getDispatcher(): Trails_Dispatcher
    {
        $trails_root = $GLOBALS['STUDIP_BASE_PATH'] . DIRECTORY_SEPARATOR . 'app';
        $trails_uri = rtrim($GLOBALS['ABSOLUTE_URI_STUDIP'], '/') . '/dispatch.php';
        $default_controller = 'default';

        return new Trails_Dispatcher($trails_root, $trails_uri, $default_controller);
    }

    private function getController(): StudipController
    {
        $dispatcher = $this->getDispatcher();
        return new class($dispatcher) extends StudipController {

        };
    }

    /**
     * @dataProvider UrlForProvider
     * @covers StudipController::url_for
     */
    public function testUrlFor(string $expected, ...$args): void
    {
        $url = $this->getController()->url_for(...$args);
        $this->assertEquals(
            $expected,
            $this->getRelativeURL($url)
        );
    }

    /**
     * @dataProvider absoluteUrlForProvider
     * @covers StudipController::url_for
     */
    public function testUrlForWithAbsoluteURL(...$args): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->getController()->url_for(...$args);
    }

    /**
     * @dataProvider UrlForProvider
     * @covers StudipController::redirect
     */
    public function testRedirect(string $expected, ...$args): void
    {
        $controller = $this->getController();
        $controller->redirect(...$args);
        $headers = $controller->get_response()->headers;

        $this->assertArrayHasKey('Location', $headers);

        $this->assertEquals(
            $expected,
            $this->getRelativeURL($headers['Location'])
        );
    }

    /**
     * @dataProvider absoluteRedirectProvider
     * @covers StudipController::redirect
     */
    public function testRedirectAbsoluteURL(bool $should_suceed, ...$args): void
    {
        if (!$should_suceed) {
            $this->expectException(InvalidArgumentException::class);
        }
        $this->getController()->redirect(...$args);
    }

    /**
     * @dataProvider UrlForProvider
     * @covers StudipController::relocate
     */
    public function testRelocate(string $expected, ...$args): void
    {
        $controller = $this->getController();
        $controller->relocate(...$args);
        $headers = $controller->get_response()->headers;

        $this->assertArrayHasKey('Location', $headers);

        $this->assertEquals(
            $expected,
            $this->getRelativeURL($headers['Location'])
        );
    }

    /**
     * @dataProvider absoluteRedirectProvider
     * @covers StudipController::relocate
     */
    public function testRelocateAbsoluteURL(bool $should_suceed, ...$args): void
    {
        if (!$should_suceed) {
            $this->expectException(InvalidArgumentException::class);
        }
        $this->getController()->relocate(...$args);
    }

    /**
     * @dataProvider UrlForProvider
     * @covers StudipController::relocate
     * @backupGlobals enabled
     */
    public function testRelocateFromDialog(string $expected, ...$args): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTP_X_DIALOG'] = true;

        $controller = $this->getController();
        $controller->relocate(...$args);
        $headers = $controller->get_response()->headers;

        $this->assertArrayHasKey('X-Location', $headers);

        $this->assertEquals(
            $expected,
            $this->getRelativeURL(rawurldecode($headers['X-Location']))
        );
    }

    /**
     * @dataProvider absoluteRedirectProvider
     * @covers StudipController::relocate
     * @backupGlobals enabled
     */
    public function testRelocateFromDialogAbsoluteURL(bool $should_suceed, ...$args): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_SERVER['HTTP_X_DIALOG'] = true;

        if (!$should_suceed) {
            $this->expectException(InvalidArgumentException::class);
        }
        $this->getController()->relocate(...$args);
    }

    /**
     * Returns a relative url for Stud.IP if given url matches.
     */
    private function getRelativeURL(string $url): string
    {
        if (strpos($url, $GLOBALS['ABSOLUTE_URI_STUDIP']) === 0) {
            return str_replace($GLOBALS['ABSOLUTE_URI_STUDIP'], '', $url);
        }
        return $url;
    }

    public function UrlForProvider(): array
    {
        return [
            '1-action'                 => ['dispatch.php/foo', 'foo'],
            '1-action-and-parameter'   => ['dispatch.php/foo?bar=42', 'foo', ['bar' => 42]],
            '1-action-and-parameters'  => ['dispatch.php/foo?bar=42&baz=23', 'foo', ['bar' => 42, 'baz' => 23]],

            '2-actions'                => ['dispatch.php/foo/bar', 'foo', 'bar'],
            '2-actions-and-parameter'  => ['dispatch.php/foo/bar?bar=42', 'foo', 'bar', ['bar' => 42]],
            '2-actions-and-parameters' => ['dispatch.php/foo/bar?bar=42&baz=23', 'foo', 'bar', ['bar' => 42, 'baz' => 23]],
        ];
    }

    public function absoluteUrlForProvider(): array
    {
        return [
            'url'                => ['https://example.org'],
            'url-and-parameters' => ['https://example.org', ['foo' => 'bar', 'baz' => 42]],
        ];
    }

    public function absoluteRedirectProvider(): array
    {
        return [
            'url'                => [true, 'https://example.org'],
            'url-and-parameters' => [false, 'https://example.org', ['foo' => 'bar', 'baz' => 42]],
        ];
    }
}

