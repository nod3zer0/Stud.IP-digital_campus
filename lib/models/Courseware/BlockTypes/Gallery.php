<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware gallery block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Gallery extends BlockType
{
    public static function getType(): string
    {
        return 'gallery';
    }

    public static function getTitle(): string
    {
        return _('Galerie');
    }

    public static function getDescription(): string
    {
        return _('Bilder aus einem Ordner im Dateibereich zeigen.');
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
            if ($typedFolder->folder_type === 'HiddenFolder' && !$typedFolder->download_allowed) {
                return $payload;
            }

            $payload['folder-type'] = $typedFolder->folder_type;

            foreach ($typedFolder->getFiles() as $folderFile) {
                $fileRef = $folderFile->getFileRef();
                $file = [];
                $file['id'] = $folderFile->id;
                $file['attributes'] = [
                    'name'          => $folderFile->name,
                    'mime-type'     => $folderFile->mime_type,
                    'filesize'      => (int) $folderFile->size,
                    'description'   => $fileRef->description,
                    'mkdate'        => date('c', $folderFile->mkdate),
                ];
                $file['relationships'] = [
                    'owner' => [
                        'data' => ['type' => 'users', 'id' => $folderFile->user_id],
                        'meta' => ['name' => $fileRef->getAuthorName()]
                        ]
                ];
                $file['meta'] = [
                    'download-url'  => $folderFile->getDownloadURL(),
                ];
    
                if ($this->filePermission($typedFolder, $file, $user) && $fileRef->isImage()) {
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

    public function initialPayload(): array
    {
        return [
            'folder_id' => '',
            'layout' => 'carousel',
            'autoplay' => 'false',
            'autoplay_timer' => '2',
            'nav' => 'true',
            'height' => '620',
            'cols' => '25',
            'show_filenames' => 'true',
            'show_description' => 'false',
            'mouseover_filenames' => 'false',
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
        if (!$payload['folder_id']) {
            return [];
        }

        $files = [];

        if ($fileRefs = \FileRef::findByFolder_id($payload['folder_id'])) {
            $files = $fileRefs;
        }

        return $files;
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
        $schemaFile = __DIR__.'/Gallery.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['multimedia'];
    }

    public static function getContentTypes(): array
    {
        return ['image'];
    }

    public static function getFileTypes(): array
    {
        return ['image'];
    }

    public static function getTags(): array
    {
        return [
            _('Bild'), _('Gestaltung'), _('Galerie'), _('Bilderkarussell'), _('Illustration'),
            _('Foto'), 'jpg', 'png', 'gif', _('Abbildung'), _('Slideshow'),
            _('Diashow'), _('Slider'), _('blättern'), _('Veranschaulichung'), _('Visualisierung')
        ];
    }
}
