<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware table of contents block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class TableOfContents extends BlockType
{
    public static function getType(): string
    {
        return 'table-of-contents';
    }

    public static function getTitle(): string
    {
        return _('Inhaltsverzeichnis');
    }

    public static function getDescription(): string
    {
        return _('Stellt auf verschiedene Arten die Unterseiten dieser Seite dar.');
    }

    public function initialPayload(): array
    {
        return [
            'style' => 'list',
            'title' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/TableOfContents.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['layout'];
    }

    public static function getContentTypes(): array
    {
        return ['link'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

    public static function getTags(): array
    {
        return [
            _('Inhalt'), _('Kacheln'), _('Layout'), _('Navigation'), _('Inhaltsnavigation'),
            _('Unterseite'), _('Liste'), _('Gliederung'), _('Struktur'), _('Dekoration'),
            _('Design'), _('Strukturierung'), _('Orientierung'), _('Übersicht')
        ];
    }
}
