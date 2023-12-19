<?php
/**
 * ExternPageCourseDownload.php - Class to provide a list of files from
 * an institutes file system.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.4
 */

class ExternPageDownload extends ExternPage
{

    public $institute;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->institute = Institute::find($this->page_config->range_id);
    }

    /**
     * @see ExternPage::getSortFields()
     */
    public function getSortFields(): array
    {
        return [
            'name'     => _('Dateiname'),
            'mkdate'   => _('Datum'),
            'size'     => _('Dateigröße'),
            'author'   => _('Autor:in'),
            'filetype' => _('Dateityp')
        ];
    }

    /**
     * @see ExternPage::getDataFields()
     */
    public function getDataFields(array $object_classes = []): array
    {
        return parent::getDataFields(
            [
                'user'
            ]
        );
    }

    /**
     * @see ExternPage::getConfigFields()
     */
    public function getConfigFields($as_array = false)
    {
        $args = '
            sort          option,
            language      option,
            folder        optionArray,
            subfolders    int
        ';
        return $as_array ? self::argsToArray($args) : $args;
    }

    /**
     * @see ExternPage::getAllowedRequestParams()
     */
    public function getAllowedRequestParams(bool $as_array = false)
    {
        $params = [
            'language',
            'sort',
            'folder',
            'subfolders',
        ];
        return $as_array ? $params : implode(',', $params);
    }

    /**
     * @see ExternPage::getMarkersContents()
     */
    public function getMarkersContents(): array
    {
        return $this->getContent();
    }

    /**
     * Returns all content of downloadable files to render the page template.
     *
     * @return array All content to render the template.
     */
    protected function getContent(): array
    {
        $content = [];
        $downloadable_file_refs = new SimpleCollection($this->getDownloadableFileRefs($this->page_config->range_id));
        $downloadable_file_refs->orderBy($this->sort);
        foreach ($downloadable_file_refs as $file_ref) {
            $content_file = [
                'ICON_URL'    => Icon::create(
                     FileManager::getIconNameForMimeType($file_ref->mime_type)
                )->asImagePath(),
                'URL'          => $file_ref->download_url,
                'NAME'         => $file_ref->name,
                'DESCRIPTION'  => $file_ref->description,
                'UPLOAD_DATE'  => $file_ref->mkdate,
                'SIZE'         => $file_ref->filesize > 1048576
                    ? round($file_ref->filesize / 1048576, 1) . ' MB'
                    : round($file_ref->filesize / 1024, 1) . ' KB'
            ];
            if ($file_ref->owner) {
                $content_owner = [
                    'USERNAME'          => $file_ref->owner->username,
                    'USERID'            => $file_ref->owner->id,
                    'FULLNAME'          => $file_ref->owner->getFullname(),
                    'FIRSTNAME'         => $file_ref->owner->vorname,
                    'LASTNAME'          => $file_ref->owner->nachname,
                    'TITLEFRONT'        => $file_ref->owner->title_front,
                    'TITLEREAR'         => $file_ref->owner->title_rear,
                ];
            }
            $content[] = array_merge(
                $content_file,
                $content_owner,
                $this->getDatafieldMarkers($file_ref->owner));
        }

        return ['FILES' => $content];
    }

    /**
     * Retrieves all file refs of downloadable files.
     *
     * @param string $institute_id The id of the institute.
     * @return array Array with file refs.
     */
    protected function getDownloadableFileRefs(string $institute_id): array
    {
        $downloadable_file_refs = [];

        $top_folder = Folder::findTopFolder($institute_id);
        $top_folder = $top_folder->getTypedFolder();

        $files = $folders = [];
        extract(FileManager::getFolderFilesRecursive($top_folder, 'nobody'));

        foreach ($files as $file) {
            $folder = $folders[$file->folder_id];
            if ($folder->isFileDownloadable($file->id, 'nobody')) {
                $downloadable_file_refs[] = $file;
            }
        }
        return $downloadable_file_refs;
    }

    public function getFullGroupNames()
    {
        $groups = [];
        foreach ($this->institute->status_groups as $status_group) {
            $groups[$status_group->id] = $status_group->name;
            $groups = array_merge($groups, $this->getGroupPaths($status_group));
        }
        return $groups;
    }

    /**
     * @param $group
     * @param string $seperator
     * @param string $pre
     * @return array
     */
    public function getGroupPaths($group, $seperator = ' > ', $pre = ''): array
    {
        $name = $pre
            ? $pre . $seperator . $group->getName()
            : $group->getName();
        $result[$group->id] = $name;
        if ($group->children) {
            foreach ($group->children as $child) {
                $result = array_merge($result, $this->getGroupPaths($child, $seperator, $name));
            }
        }
        return $result;
    }

    /**
     * Returns an array of paths from top folder to all folders below.
     *
     * @return array Array of concatenated folder names.
     */
    public function get_concatenated_folders(): array
    {
        $top_folder = Folder::findTopFolder($this->page_config->range_id);
        $top_folder = $top_folder->getTypedFolder();
        $folders = FileManager::getReadableFolders($top_folder, 'nobody');
        $folder_names = [];
        $get_parent_folder_name = function ($folder, $folder_id) use (&$get_parent_folder_name, &$folders, &$folder_names) {
            $folder_names[$folder_id][] = $folder->name;
            if ($folder->parent_id) {
                $get_parent_folder_name($folders[$folder->parent_id], $folder_id);
            } else {
                $folder_names[$folder_id] = implode(' > ', array_reverse($folder_names[$folder_id]));
            }
        };
        array_walk($folders, $get_parent_folder_name);
        return $folder_names;
    }

}
