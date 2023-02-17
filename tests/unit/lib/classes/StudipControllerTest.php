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
        return new StudipControllerTestController($dispatcher);
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
     * @dataProvider actionUrlProvider
     * @covers StudipController::action_url
     */
    public function testActionUrl(string $expected, ...$args): void
    {
        $url = $this->getController()->action_url(...$args);
        $this->assertEquals(
            $expected,
            $this->getRelativeURL($url)
        );

        $link = $this->getController()->action_link(...$args);
        $this->assertEquals(
            htmlspecialchars($expected),
            $this->getRelativeURL($link)
        );
    }

    /**
     * @dataProvider RedirectProvider
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
     * @dataProvider RedirectProvider
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
     * @dataProvider RedirectProvider
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
     * @covers StudipController::get_assigned_variables
     */
    public function testVariableAssignment(): void
    {
        $controller = $this->getController();

        // Set and test assignments
        $controller->foo = 'bar';
        $this->assertEquals('bar', $controller->foo);

        $controller->bar = 42;
        $this->assertEquals(42, $controller->bar);

        $controller->baz = [];
        $controller->baz[] = 23;
        $this->assertEquals([23], $controller->baz);

        $controller->bad[] = 23;
        $this->assertEquals([23], $controller->bad);

        // Test fetching all variables
        $variables = $controller->get_assigned_variables();

        $this->assertIsArray($variables);

        // - Implicit variables
        $this->assertArrayHasKey('controller', $variables);
        $this->assertArrayHasKey('current_action', $variables);

        // - Explicit variables
        $this->assertArrayHasKey('foo', $variables);
        $this->assertEquals('bar', $variables['foo']);

        $this->assertArrayHasKey('bar', $variables);
        $this->assertEquals(42, $variables['bar']);

        $this->assertArrayHasKey('baz', $variables);
        $this->assertCount(1, $variables['baz']);
        $this->assertEquals([23], $variables['baz']);

        $this->assertArrayHasKey('bad', $variables);
        $this->assertCount(1, $variables['bad']);
        $this->assertEquals([23], $variables['bad']);
    }

    /**
     * @dataProvider argumentValidationProvider
     * @covers StudipController::validate_args
     */
    public function testArgumentValidation(array $args, array $expected, ?array $types): void
    {
        $this->getController()->validate_args($args, $types);

        $this->assertEquals($expected, $args);
    }

    /**
     * @dataProvider argumentValidationOptionProvider
     * @covers StudipController::validate_args
     */
    public function testArgumentValidationOption(string $should_fail): void
    {
        $args = [$should_fail];

        $this->expectException(Trails_Exception::class);
        $this->getController()->validate_args($args);
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
            '0-action'                 => ['dispatch.php/studip_controller_test/foo'],
            '0-action-and-parameter'   => ['dispatch.php/studip_controller_test/foo?bar=42', '', ['bar' => 42]],
            '1-action'                 => ['dispatch.php/foo', 'foo'],
            '1-action-and-parameter'   => ['dispatch.php/foo?bar=42', 'foo', ['bar' => 42]],
            '1-action-and-parameters'  => ['dispatch.php/foo?bar=42&baz=23', 'foo', ['bar' => 42, 'baz' => 23]],

            '2-actions'                => ['dispatch.php/foo/bar', 'foo', 'bar'],
            '2-actions-and-parameter'  => ['dispatch.php/foo/bar?bar=42', 'foo', 'bar', ['bar' => 42]],
            '2-actions-and-parameters' => ['dispatch.php/foo/bar?bar=42&baz=23', 'foo', 'bar', ['bar' => 42, 'baz' => 23]],

            'fragment'                 => ['dispatch.php/foo/bar/42/23#jump', 'foo/bar/42/23#jump'],
            'fragment-and-parameters'  => ['dispatch.php/foo/bar/42/23#jump', 'foo/bar#jump', 42, 23],
            'url-encoding-parameters'  => ['dispatch.php/foo/bar/%3Fabc/%2F', 'foo/bar', '?abc', '/'],
        ];
    }

    public function actionUrlProvider(): array
    {
        return [
            'action'                   => ['dispatch.php/studip_controller_test/foo', 'foo'],
            'action-and-parameter'     => ['dispatch.php/studip_controller_test/foo/23', 'foo/23'],
            'action-and-parameters'    => ['dispatch.php/studip_controller_test/foo/23?bar=42', 'foo/23', ['bar' => 42]],

            'fragment'                 => ['dispatch.php/studip_controller_test/foo/42/23#jump', 'foo/42/23#jump'],
            'fragment-and-parameters'  => ['dispatch.php/studip_controller_test/foo/42/23#jump', 'foo#jump', 42, 23],
            'url-encoding-parameters'  => ['dispatch.php/studip_controller_test/foo/%3Fabc/%2F', 'foo', '?abc', '/'],
        ];
    }

    public function RedirectProvider(): array
    {
        $result = $this->UrlForProvider();
        unset($result['0-action']);
        return $result;
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

    public function argumentValidationProvider(): array
    {
        return [
            'default' => [['2', '2,5', 'abc', 'a-b-c'], ['2', '2,5', 'abc', 'a-b-c'], null],
            'typed'   => [['2.5', '2.5', 'abc', 'a-b-c'], [2, 2.5, 'abc', 'a-b-c'], ['int', 'float', 'option', 'string']],
            'int'     => [['1.5', '2.5', '-1.5'], [1, 2, -1], ['int', 'int', 'int']],
            'float'   => [['1.5', '2.5', '-1.5'], [1.5, 2.5, -1.5], ['float', 'float', 'float']],
            'option'   => [['a-b', '1,2,3', 'foobar'], ['a-b', '1,2,3', 'foobar'], ['option', 'option', 'option']],
            'string'   => [['a', 'b!', 'http://c#?'], ['a', 'b!', 'http://c#?'], ['string', 'string', 'string']],
        ];
    }

    public function argumentValidationOptionProvider(): array
    {
        return [
            'space'               => [' '],
            'float'               => ['1.5'],
            'url'                 => ['http://example.org'],
            'special'             => ['a#b'],
            'little-bobby-tables' => ["Robert'); DROP TABLE STUDENTS; --"],
        ];
    }
}

class StudipControllerTestController extends StudipController
{
    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);

        $this->current_action = 'foo';
    }

    public function foo_action(int $int, float $float, string $option, string $string): void
    {
        $this->render_text('In a test?');
    }
}
