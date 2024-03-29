<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware date block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Date extends BlockType
{
    public static function getType(): string
    {
        return 'date';
    }

    public static function getTitle(): string
    {
        return _('Termin');
    }

    public static function getDescription(): string
    {
        return _('Zeigt einen Termin oder Countdown an.');
    }

    public function initialPayload(): array
    {
        return [
            'timestamp' => time() * 1000,
            'style' => 'countdown',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Date.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['layout'];
    }

    public static function getContentTypes(): array
    {
        return ['data'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

    public static function getTags(): array
    {
        return [
            _('Darstellung'), _('Layout'), _('Erinnerung'), _('Datum'), _('Kalender'),
            _('Dekoration'), _('Termin'), _('Countdown'), _('Uhr'), _('Ziffer'), _('Zahl'),
            _('Uhrzeit'), _('Tag'), _('Stunde'), _('Minute'), _('Veranstaltungszeit'),
            _('live'), _('Treffen'), _('VK'), _('Deadline'), _('Konferenz'), _('Meeting'),
            _('Session'), _('Sitzung'), _('Verabredung'), _('Abgabezeitpunkt'),
            _('Einreichungsfrist'), _('Frist'), _('Bearbeitungszeit')
        ];
    }
}
