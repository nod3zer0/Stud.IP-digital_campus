<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware folder block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Folder extends BlockType
{
    public static function getType(): string
    {
        return 'folder';
    }

    public static function getTitle(): string
    {
        return _('Dateiordner');
    }

    public static function getDescription(): string
    {
        return _('Stellt einen Ordner aus dem Dateibereich zur Verfügung.');
    }

    public function initialPayload(): array
    {
        return [
            'type' => '', // store folder type to detect changes
            'folder_id' => '',
            'title' => '',
        ];
    }

    /**
     * Returns the decoded payload of the block associated with this instance.
     *
     * @return mixed the decoded payload
     */
    public function getPayload()
    {
        $user = \User::findCurrent();
        $payload = $this->decodePayloadString($this->block['payload']);

        $folder = \Folder::find($payload['folder_id']);
        $payload['folder-type'] = null;
        $payload['files'] = [];

        if ($folder) {
            $typedFolder = $folder->getTypedFolder();
            $payload['folder-type'] = $typedFolder->folder_type;

            foreach ($typedFolder->getFiles() as $folderFile) {
                $file['id'] = $folderFile->id;
                $file['attributes'] = [
                    'name'          => $folderFile->name,
                    'mime-type'     => $folderFile->mime_type,
                    'filesize'      => (int) $folderFile->size,
                    'mkdate'        => date('c', $folderFile->mkdate),
                ];
                $file['relationships'] = [
                    'owner' => [
                        'data' => ['type' => 'users', 'id' => $folderFile->user_id],
                        'meta' => ['name' => $folderFile->getFileRef()->getAuthorName()]
                        ]
                ];
                $file['meta'] = [
                    'download-url'  => $folderFile->getDownloadURL(),
                ];
    
                if ($this->filePermission($typedFolder, $file, $user)) {
                    array_push($payload['files'], $file);
                }
            }
        }

        return $payload;
    }

    private function filePermission($typedFolder, $file, $user): bool
    {
        return $typedFolder->folder_type !== 'HomeworkFolder' || $user->id === $file['relationships']['owner']['data']['id'] || $typedFolder->isReadable($user->id);
    }


    /**
     * get all files related to this block.
     *
     * @return \FileRef[] list of file references realted to this block
     */
    public function getFiles(): array
    {
        $payload = $this->getPayload();
        if (!$payload['folder_id']) {
            return [];
        }

        return \FileRef::findByFolder_id($payload['folder_id']);
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if ('' != $payload['folder_id']) {
            $payload['folder_id'] = $this->copyFolderById($payload['folder_id'], $rangeId);
        }

        return $payload;
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Folder.json';

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
            _('Hausaufgaben'), _('Upload'), _('Download'), _('Datei'), _('hochladen'),
            _('herunterladen'), _('einstellen'), 'pdf', 'ppt', 'doc', 'xls',
            'odt', 'ods', 'odp', 'PowerPoint', 'Word', 'Excel', 'LibreOffice',
            'OpenOffice', _('Ordner'), _('Dokument'), _('Link'), _('verlinken'), _('Skript'),
            _('Material'), _('Zusatz'), 'zip', _('Mappe'), _('Sammlung'), _('Sicherung'),
            _('Literatur'), _('Zusatzmaterial'), _('Semesterapparat'), _('Infomaterial')
        ];
    }
}
