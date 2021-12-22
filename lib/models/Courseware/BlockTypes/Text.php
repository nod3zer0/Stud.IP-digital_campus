<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;
require_once 'lib/classes/Markup.class.php';

/**
 * This class represents the content of a Courseware text block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Text extends BlockType
{
    public static function getType(): string
    {
        return 'text';
    }

    public static function getTitle(): string
    {
        return _('Text');
    }

    public static function getDescription(): string
    {
        return _('Erstellen von Inhalten mit dem WYSIWYG-Editor.');
    }

    public function initialPayload(): array
    {
        return ['text' => ''];
    }

    public function getPayload()
    {
        $payload = parent::getPayload();

        $payload['text'] = \Studip\Markup::purifyHtml(\Studip\Markup::markAsHtml($payload['text']));

        return $payload;
    }

    public function setPayload($payload): void
    {
        $payload['text'] = \Studip\Markup::purifyHtml(\Studip\Markup::markAsHtml($payload['text']));

        parent::setPayload($payload);
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Text.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    /**
     * get all files related to this block.
     *
     * @return \FileRef[] list of file references realted to this block
     */
    public function getFiles(): array
    {
        $payload = $this->getPayload();
        $document = new \DOMDocument();
        $files = [];

        if ($payload['text']) {
            $document->loadHTML($payload['text']);
            $imageElements = $document->getElementsByTagName('img');
            foreach ($imageElements as $element) {
                if (!$element instanceof \DOMElement || !$element->hasAttribute('src')) {
                    continue;
                }
                $file = $this->extractFile($element->getAttribute('src'));
                if ($file !== null) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();
        $document = new \DOMDocument();

        if ($payload['text']) {
            $document->loadHTML(mb_convert_encoding($payload['text'], 'HTML-ENTITIES', 'UTF-8'));
            $imageElements = $document->getElementsByTagName('img');
            foreach ($imageElements as $element) {
                if (!$element instanceof \DOMElement || !$element->hasAttribute('src')) {
                    continue;
                }
                $file = $this->extractFile($element->getAttribute('src'));
                if ($file !== null) {
                    $file_copy_id = $this->copyFileById($file->id, $rangeId);
                    $element->setAttribute('src', \FileRef::find($file_copy_id)->getDownloadURL());
                }
            }
            $payload['text'] = $document->saveHTML();
        }

        return $payload;
    }

    public static function getCategories(): array
    {
        return ['basis', 'text'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

        /**
     * Calls a callback if a given URL is an internal URL.
     *
     * @param string   $url      The url to check
     * @param callable $callback A callable to execute
     *
     * @return mixed The return value of the callback or null if the callback
     *               is not executed
     */
    private function applyCallbackOnInternalUrl($url, $callback)
    {
        if (! \Studip\MarkupPrivate\MediaProxy\isInternalLink($url)) {
            return null;
        }
        $components = parse_url($url);
        if (
            isset($components['path'])
            && substr($components['path'], -13) == '/sendfile.php'
            && isset($components['query'])
            && $components['query'] != ''
        ) {
            parse_str($components['query'], $queryParams);

            return $callback($components, $queryParams);
        }

        return null;
    }

    private function extractFile($url)
    {
        return $this->applyCallbackOnInternalUrl($url, function ($components, $queryParams) {
            if (isset($queryParams['file_id'])) {
                $file_ref = new \FileRef($queryParams['file_id']);
                return $file_ref;

            }

            return array();
        });
    }
}
