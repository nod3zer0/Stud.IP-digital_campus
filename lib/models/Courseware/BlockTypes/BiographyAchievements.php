<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware biography achievements block.
 *
 * @author  Robin Winkler <rwinkler@uos.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.2
 */
class BiographyAchievements extends BlockType
{
    public static function getType(): string
    {
        return 'biography-achievements';
    }

    public static function getTitle(): string
    {
        return _('Erfolge');
    }

    public static function getDescription(): string
    {
        return _('Zeigt verschiedene Arten von erreichten Erfolgen an.');
    }

    public function initialPayload(): array
    {
        return [
            'type' => 'certificate',
            'title' => '',
            'date' => time() * 1000,
            'end_date' => time() * 1000,
            'role' => '',
            'description' => ''
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/BiographyAchievements.json';

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
            _('Portfolio'), _('Lebenslauf'), _('Zertifikat'), _('Bewerbung'), _('Karriere'), _('Job'),
            _('Beruf'), _('Achievement'), _('Badges'), _('Auszeichnung'), _('Ergebnis'), _('Sammelmappe'),
            _('Akkreditierung'), _('Buch'), _('Veröffentlichung'), _('Mitgliedschaft'), _('Abzeichen')
        ];
    }
}
