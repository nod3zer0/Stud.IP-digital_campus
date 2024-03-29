<?php
/**
 * MvvFileFileref.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 *
 * @property array $id alias for pk
 * @property string $mvvfile_id database column
 * @property string $file_language database column
 * @property string $name database column
 * @property string $fileref_id database column
 * @property string|null $author_id database column
 * @property string|null $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property MvvFile $mvv_file belongs_to MvvFile
 * @property FileRef $file_ref belongs_to FileRef
 * @property-read mixed $filetype additional field
 * @property-read mixed $filename additional field
 */

class MvvFileFileref extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_files_filerefs';

        $config['belongs_to']['mvv_file'] = array(
            'class_name' => MvvFile::class,
            'foreign_key' => 'mvvfile_id',
            'assoc_func' => 'findCached',
        );

        $config['belongs_to']['file_ref'] = array(
            'class_name' => FileRef::class,
            'foreign_key' => 'fileref_id',
        );
        $config['additional_fields']['filetype']['get'] = 'getFiletype';
        $config['additional_fields']['filename']['get'] = 'getFilename';


        parent::configure($config);
    }

    /**
     * Returns the filename of the document based on its fileref.
     *
     * @return string Name of the file.
     */
    public function getFilename(): string
    {
        if ($this->file_ref) {
            $filetype = $this->file_ref->getFileType();
            return $filetype->getFilename();
        }

        throw new Exception("Could not load file ref for file");
    }

    /**
     * Returns the filetype of the document based on its mimetype.
     *
     * @return bool|string Returns false on failure, otherwise the name of the filetype.
     */
    public function getFiletype()
    {
        $application_category = [
            'PDF'     => ['pdf'],
            'Powerpoint'     => ['powerpoint','presentation'],
            'Excel'   => ['excel', 'spreadsheet', 'csv'],
            'Word'    => ['word', 'wordprocessingml', 'opendocument.text', 'rtf'],
            'Archiv' => ['zip', 'rar', 'arj', '7z'],
        ];

        if (!$this->file_ref) {
            return '';
        }
        $filetype = $this->file_ref->getFileType();
        if ($filetype instanceof URLFile) {
            return _('Link');
        } else {
            $mime_type = $filetype->getMimeType();
            if (!$mime_type) {
                return _('sonstiges');
            } else {
                list($category, $type) = explode('/', $mime_type, 2);
                if ($category == 'application') {
                    foreach ($application_category as $name => $type_name) {
                        if (preg_match('/' . implode('|', $type_name) . '/i', $type)) {
                            return $name;
                        }
                    }
                } elseif ($category == 'image') {
                    return _('Bild');
                } else {
                    return $category;
                }
            }
        }

        return false;
    }

    protected function logChanges($action = null)
    {
        $log_action = 'MVV_FILEREF_' . mb_strtoupper($action);
        $affected = $this->id;
        $info = ['mvv_files_filerefs.*'];
        $debug_info = $this->getDisplayName();
        if ($action === 'update') {
            $logged_fields = [
                'file_language',
                'name',
            ];
            foreach ($logged_fields as $logged_field) {
                if ($this->isFieldDirty($logged_field)) {
                    $info[] = $logged_field
                        . ': ' . ($this->getValue($logged_field) ?? '-')
                        . ' (' . ($this->getPristineValue($logged_field) ?? '-')
                        . ')';
                }
            }
        }
        StudipLog::log($log_action, $affected, null, implode(' | ', $info), $debug_info);
    }
}
