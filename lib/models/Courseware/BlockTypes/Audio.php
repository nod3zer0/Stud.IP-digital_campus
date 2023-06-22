<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware audio block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Audio extends BlockType
{
    public static function getType(): string
    {
        return 'audio';
    }

    public static function getTitle(): string
    {
        return _('Audio');
    }

    public static function getDescription(): string
    {
        return _('Spielt eine Audiodatei aus dem Dateibereich oder von ' .
                 'einer URL ab und ermöglicht Audioaufnahmen direkt im Block.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => 'Audio',
            'source' => 'studip_folder',
            'file_id' => '',
            'folder_id' => '',
            'web_url' => '',
            'recorder_enabled' => false
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Audio.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    /**
     * get all files related to this block.
     *
     * @return \FileRef[] list of file references related to this block
     */
    public function getFiles(): array
    {
        $payload = $this->getPayload();
        if (!$payload['file_id'] && !$payload['folder_id']) {
            return [];
        }

        if ($payload['file_id']) {
            $files = [];

            if ($fileRef = \FileRef::find($payload['file_id'])) {
                $files[] = $fileRef;
            }

            return $files;
        } else {
            return \FileRef::findByFolder_id($payload['folder_id']);
        }
    }

    public function copyPayload(string $rangeId = '', $payload = null): array
    {
        if (!$payload) {
            $payload = $this->getPayload();
        }

        if (!empty($payload['file_id'])) {
            $payload['file_id'] = $this->copyFileById($payload['file_id'], $rangeId);
        }

        return $payload;
    }

    public static function getCategories(): array
    {
        return ['multimedia', 'interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['audio'];
    }

    public static function getFileTypes(): array
    {
        return ['audio'];
    }

    public static function getTags(): array
    {
        return [
            'mp3', _('Interaktion'), _('Sprachnachricht'), _('Audiogalerie'),
            _('Studierende aktivieren'), _('Austausch'), _('Musik'), _('Sprache'),
            _('Ton'), _('Tonaufzeichnung'), _('O Ton'), _('Zitat'), _('Sprachmemo'),
            _('Tonaufnahme'), _('Vorstellungsrunde'), _('Podcast'), _('Audioforum'),
            _('Sprachaufzeichnung'), _('Aufzeichnung'), _('Multimedia'),
            _('Inhalt erstellen'), _('Studierende interagieren'), _('Einstieg'),
            _('hochladen'), _('Aufnahme'), _('Abspielen'), _('Aktivierung'),
            _('Interview'), _('Radio'), _('Tonspur'), _('Stimme'), _('Sprachaufnahme')
        ];
    }
}
