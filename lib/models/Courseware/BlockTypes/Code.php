<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware code block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Code extends BlockType
{
    public static function getType(): string
    {
        return 'code';
    }

    public static function getTitle(): string
    {
        return _('Quelltext');
    }

    public static function getDescription(): string
    {
        return _('Quelltext wird seiner Syntax entsprechend farblich hervorgehoben.');
    }

    public function initialPayload(): array
    {
        return [
            'content' => '',
            'lang' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Code.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['text'];
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
            _('Quelltext'), _('Syntaxhervorhebung'), _('Monospace'),
            _('Codeblock'), _('Programmcode'), _('programmieren'), _('Pseudocode'), 'Python',
            'C', 'Java', 'Prolog', 'Lisp', 'Javascript', 'Haskell', 'C++',
            'C#', 'Objective C', 'Rust', 'Assembler', _('Algorithmus')
        ];
    }
}
