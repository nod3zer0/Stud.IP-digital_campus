<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware error block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Error extends BlockType
{
    public static function getType(): string
    {
        return 'error';
    }

    public static function getTitle(): string
    {
        return _('Fehler');
    }

    public static function getDescription(): string
    {
        return _('Zeigt eine Fehlemeldung an.');
    }

    public function initialPayload(): array
    {
        return [];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Error.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return [];
    }

    public static function getContentTypes(): array
    {
        return [];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
