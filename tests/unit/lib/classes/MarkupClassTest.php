<?php
/**
 * markup_class_test.php - Unit tests for the Markup class.
 **
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category    Stud.IP
 * @copyright   (c) 2014 Stud.IP e.V.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @since       File available since Release 3.0
 * @author      Robert Costa <rcosta@uos.de>
 */
require_once 'tests/unit/fakeserver.php';

# needed by visual.inc.php
require_once 'lib/classes/DbView.class.php';
require_once 'lib/classes/TreeAbstract.class.php';

# needed by Markup.class.php
require_once 'lib/visual.inc.php';
require_once 'lib/classes/Config.class.php';

# class and functions that are tested by this script
require_once 'lib/classes/Markup.class.php';

# Seminar_Session cannot be mocked since it uses static functions.
# Also, including phplib_local.inc.php, where Seminar_Session is
# defined, introduces a massive amount of dependencies that are otherwise
# completely unneeded for testing the Markup class.
# Instead, create a fake class.
# => But note, this will fail if another test case does the same thing!
class Seminar_Session
{
    public static function is_current_session_authenticated()
    {
        return true;
    }
}

/**
 * Test case for Markup class.
 */
class MarkupClassTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider removeProvider
     */
    public function testRemoveHTML(string $input, string $expected): void
    {
        Config::set(new Config(['WYSIWYG' => true]));

        $this->assertEquals($expected, Studip\Markup::removeHtml(Studip\Markup::markAsHtml($input)));
    }

    public function testGetMediaUrl()
    {
        # mock class Config
        $configStub = $this->getMockBuilder('Config')
        ->disableOriginalConstructor()
        ->getMock();

        $properties = [];

        $configStub->expects($this->any())
        ->method('__get')
        ->will($this->returnCallback(function ($property) use (&$properties) {
            return $properties[$property];
        }));

        $configStub->expects($this->any())
        ->method('__set')
        ->will($this->returnCallback(function ($property, $value) use (&$properties) {
            $properties[$property] = $value;
            return $properties[$property];
        }));

        Config::set($configStub);

        # exceptions
        $namespace = 'Studip\MarkupPrivate\MediaProxy\\';
        $invalidInternalLink = $namespace . 'InvalidInternalLinkException';
        $externalMediaDenied = $namespace . 'ExternalMediaDeniedException';

        # URLs
        $sendfile = 'sendfile.php?type=0&file_id=9eea7ca20cba01dd4ea394b3b53027cc&file_name=image.png';
        $wiki = 'wiki.php?cid=a07535cf2f8a72df33c12ddfa4b53dde&view=show';
        $wikipediaLogo = 'http://upload.wikimedia.org/wikipedia/meta/0/08/Wikipedia-logo-v2_1x.png';
        $proxy = 'dispatch.php/media_proxy?url=';
        $proxiedWikipediaLogo = $proxy . 'http%3A%2F%2Fupload.wikimedia.org%2Fwikipedia%2Fmeta%2F0%2F08%2FWikipedia-logo-v2_1x.png';

        # domains
        $domains = [
            'org' => 'example.org/studip',
            'home' => 'example.org/~home',
            'net' => 'example.net/studip',
        ];

        $getUrl = function ($domainKey, $path) use (&$domains) {
            return 'http://' . $domains[$domainKey] . '/' . $path;
        };

        # run various tests
        $index = 0;
        foreach ([
            [
                'in' => $getUrl('org', 'image.jpg'),
                'exception' => $invalidInternalLink,
                'uri' => $getUrl('org', 'index.php'),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('org', $sendfile),
                'uri' => $getUrl('org', 'index.php'),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('home', $sendfile),
                'uri' => $getUrl('home', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $getUrl('org', $sendfile),
                'out' => $getUrl('net', $sendfile),
                'uri' => $getUrl('net', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $wikipediaLogo,
                'out' => $wikipediaLogo,
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'allow'
            ],
            [
                'in' => $wikipediaLogo,
                'exception' => $externalMediaDenied,
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'deny'
            ],
            [
                'in' => $wikipediaLogo,
                'out' => $getUrl('org', $proxiedWikipediaLogo),
                'uri' => $getUrl('org', $wiki),
                'domains' => $domains,
                'externalMedia' => 'proxy'
            ],
        ] as $test) {
            $index++;

            # fake Stud.IP web server set-up
            fakeServer($test['uri'], $test['domains']);
            Config::get()->LOAD_EXTERNAL_MEDIA = $test['externalMedia'];
            //echoWebGlobals(); // call to help with debugging

            # test getMediaUrl
            try {
                $out = Studip\MarkupPrivate\MediaProxy\getMediaUrl($test['in']);

                if (isset($test['exception'])) {
                    $this->fail(
                        'Test ' . $index . ' did not raise '
                        . $test['exception'] . '. Output: ' . $out . '.'
                    );
                }
            } catch (PHPUnit\Framework\Error\Notice $e) {
                throw $e;
            } catch (Exception $e) {
                if ( !isset($test['exception'])) {
                    $this->fail(
                        'Test ' . $index . ' raised ' . get_class($e) . '.'
                    );
                }
                if (get_class($e) !== $test['exception']) {
                    $this->fail(
                        'Test ' . $index . ' raised ' . get_class($e)
                        . ' instead of ' . $test['exception'] . '.'
                    );
                }
            }
            if (isset($test['out'])) {
                $this->assertEquals($test['out'], $out, 'Test ' . $index);
            }
        }
    }

    public static function removeProvider(): array
    {
        return [
            'plain text' => ['plain text', 'plain text'],
            'paragraph only' => ['<p>paragraph only</p>', 'paragraph only'],

            'link: no href' => ['<a>no href</a>', 'no href'],
            'link: empty' => ['<a href=""></a>', ''],
            'link: empty href' => ['<a href="">empty href</a>', 'empty href'],
            'link: href only' => ['<a href="href only" />', '[ href%20only ]'],
            'link: href end-tag' => ['<a href="href end-tag"></a>', '[ href%20end-tag ]'],
            'link: href and text' => ['<a href="http://href.de">and text</a>', '[ http://href.de ]and text'],
            'link: before and text after' => ['before <a href="http://href.de">and text</a> after', 'before [ http://href.de ]and text after'],

            'image: no src' => ['<img>no src</img>', 'no src'],
            'image: src only' => ['<img src="src only" />', '[ src%20only ]'],
            'image: src end-tag' => ['<img src="src end-tag"></img>', '[ src%20end-tag ]'],
            'image: src and text' => ['<img src="http://src.de">and text</a>', '[ http://src.de ]and text'],
            'image: before and text after' => ['before <img src="http://src.de">and text</img> after', 'before [ http://src.de ]and text after'],

            // some "real" urls
            'real link' => ['<a href="https://example.org/">Example', '[ https://example.org/ ]Example'],
            'real image' => ['<img src="https://example.org/image.png">', '[ https://example.org/image.png ]'],
            'real link and image' => [
                '<p>link <a href="http://example.org">Example-Domain</a> and picture <img src="https://example.org/image.png"></p>',
                'link [ http://example.org ]Example-Domain and picture [ https://example.org/image.png ]',
            ],

            // Line breaks
            'html: ul' => [\Studip\Markup::HTML_MARKER . '<ul><li>1</li><li>2</li></ul><p>3</p>', "1\n2\n\n3"],
            'html: ol' => [\Studip\Markup::HTML_MARKER . '<ol><li>1</li><li>2</li></ol><p>3</p>', "1\n2\n\n3"],
            'html: br' => [\Studip\Markup::HTML_MARKER . '1<br>2<br>3', "1\n2\n3"],
            'html: div' => [\Studip\Markup::HTML_MARKER . '<div>1</div><div>2</div><div>3</div>', "1\n\n2\n\n3"],
            'html: p' => [\Studip\Markup::HTML_MARKER . '<p>1</p><p>2</p><p>3</p>', "1\n\n2\n\n3"],
        ];
    }
}
