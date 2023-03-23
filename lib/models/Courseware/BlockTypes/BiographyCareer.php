<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware biography career block.
 *
 * @author  Robin Winkler <rwinkler@uos.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.2
 */
class BiographyCareer extends BlockType
{
    public static function getType(): string
    {
        return 'biography-career';
    }

    public static function getTitle(): string
    {
        return _('Karriere');
    }

    public static function getDescription(): string
    {
        return _('Stellt die Stationen Ihrer schulischen, akademischen und beruflichen Qualifikationen, sowie Ihre Berufserfahrung dar.');
    }

    public function initialPayload(): array
    {
        return [
            'sort' => 'asc',
            'items' => [[
                'date' => '',
                'title' => '',
                'description' => '',
                'type' => 'school',
                'qualification' => '',
                'focus' => '',
                'skills' => '',
                'employer' => '',
                'job' => '',
            ]]
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/BiographyCareer.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['biography'];
    }

    public static function getContentTypes(): array
    {
        return ['data'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
