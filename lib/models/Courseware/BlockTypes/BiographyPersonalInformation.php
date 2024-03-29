<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware biography personal information block.
 *
 * @author  Robin Winkler <rwinkler@uos.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.2
 */
class BiographyPersonalInformation extends BlockType
{
    public static function getType(): string
    {
        return 'biography-personal-information';
    }

    public static function getTitle(): string
    {
        return _('Persönliche Informationen');
    }

    public static function getDescription(): string
    {
        return _('Zeigt persönliche Daten an.');
    }

    public function initialPayload(): array
    {
        return [
            'name' => '',
            'birthplace' => '',
            'birthday' => time() * 1000,
            'gender' => 'none',
            'status' => 'none'
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/BiographyPersonalInformation.json';

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
            _('Job'), _('Beruf'), _('Präsentation'), _('Geburtstag'), _('Geburtsort'), _('Daten'),
            _('Person'), _('Sammelmappe'), _('Biografie'), _('Familienstand')
        ];
    }
}
