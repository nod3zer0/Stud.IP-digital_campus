<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware download block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Download extends BlockType
{
    public static function getType(): string
    {
        return 'download';
    }

    public static function getTitle(): string
    {
        return _('Download');
    }

    public static function getDescription(): string
    {
        return _('Stellt eine Datei aus dem Dateibereich zum Download bereit.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => '',
            'info' => '',
            'success' => '',
            'grade' => false,
            'file_id' => '',
        ];
    }

    /**
     * get all files related to this bloc.
     *
     * @return \FileRef[] list of file references realted to this block
     */
    public function getFiles(): array
    {
        $payload = $this->getPayload();
        if (!$payload['file_id']) {
            return [];
        }

        $files = [];

        if ($fileRef = \FileRef::find($payload['file_id'])) {
            $files[] = $fileRef;
        }

        return $files;
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

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Download.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['files'];
    }

    public static function getContentTypes(): array
    {
        return ['files'];
    }

    public static function getFileTypes(): array
    {
        return ['all'];
    }

    public static function getTags(): array
    {
        return [
            _('Dateiliste'), _('Dateiablage'), _('Datei'), _('Teilen'), _('Ablegen'),
            _('Upload'), _('Skript'), ('herunterladen'), _('Medien'), _('Material'),
            _('Filesharing'), _('hochladen'), _('Dokument'), _('Dateibereich'), 'pdf',
            'ppt', 'doc', 'xls', 'odt', 'ods', 'odp', 'PowerPoint',
            'Word', 'Excel', 'LibreOffice', 'OpenOffice', _('Präsentation'),
            _('Sicherung'), _('Literatur'), _('Zusatzmaterial'), _('Semesterapparat'),
            _('Infomaterial')
        ];
    }
}
