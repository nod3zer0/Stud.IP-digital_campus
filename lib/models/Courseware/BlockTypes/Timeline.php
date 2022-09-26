<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware timeline block.
 *
 * @author Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Timeline extends BlockType
{
    public static function getType(): string
    {
        return 'timeline';
    }

    public static function getTitle(): string
    {
        return _('Zeitstrahl');
    }

    public static function getDescription(): string
    {
        return _('Kann beliebig viele Ereignisse in zeitlicher Reihenfolge darstellen.');
    }

    public function initialPayload(): array
    {
        return [
            'sort' => 'asc',
            'dateformat' => 'year',
            'items' => [[
                'date' => '',
                'time' => '',
                'color' => 'studip-blue',
                'icon' => 'courseware',
                'title' => '',
                'description' => ''
            ]]
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Timeline.json';

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
}
