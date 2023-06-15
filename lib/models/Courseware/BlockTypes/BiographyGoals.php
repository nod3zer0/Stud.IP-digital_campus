<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware biography personal goals block.
 *
 * @author  Robin Winkler <rwinkler@uos.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.2
 */
class BiographyGoals extends BlockType
{
    public static function getType(): string
    {
        return 'biography-goals';
    }

    public static function getTitle(): string
    {
        return _('Ziele');
    }

    public static function getDescription(): string
    {
        return _('Präsentiert eines Ihrer Ziele.');
    }

    public function initialPayload(): array
    {
        return [
            'type' => 'personal',
            'description' => ''
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/BiographyGoals.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['biography'];
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
            _('Portfolio'), _('Lebenslauf'), _('Zertifikat'), _('Bewerbung'), _('Qualifikation'),
            _('Job'), _('Beruf'), _('Zielstellung'), _('Fortschritt'), _('Bewertung'),
            _('Selbsteinschätzung'), _('Review'), _('Kompetenzen'), _('Motivation'), _('Sammelmappe'),
            _('persönlich'), _('Schulisches'), _('Berufliches'), _('Akademisches'), _('Abschluss')
        ];
    }
}
