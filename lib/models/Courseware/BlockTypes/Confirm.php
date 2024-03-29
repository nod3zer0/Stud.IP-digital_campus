<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware confirm block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Confirm extends BlockType
{
    public static function getType(): string
    {
        return 'confirm';
    }

    public static function getTitle(): string
    {
        return _('Bestätigung');
    }

    public static function getDescription(): string
    {
        return _('Vom Lernenden bestätigen lassen, dass der Inhalt betrachtet wurde.');
    }

    public function initialPayload(): array
    {
        return [
            'text' => 'Ich habe diese Seite gelesen.',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Confirm.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['text', 'interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

    public static function getTags(): array
    {
        return [
            _('Fortschritt'), _('Review'), _('Markierung'), _('Kontrolle'), _('Häkchen'),
            _('Sperre'), _('Lesebestätigung'), _('Selbstkontrolle'), _('Check'),
            _('Zustimmung'), _('Vollständigkeit'), _('Abschluss'), _('Level')
        ];
    }
}
