<?php
/**
 * MvvFileRange.php
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
 * @property string $range_id database column
 * @property-read mixed $range_type additional field
 * @property int|null $position database column
 * @property string|null $author_id database column
 * @property string|null $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property MvvFile $mvv_file belongs_to MvvFile
 */

class MvvFileRange extends ModuleManagementModel
{
    /**
     * @param array $config
     */
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_files_ranges';

        $config['belongs_to']['mvv_file'] = array(
            'class_name' => MvvFile::class,
            'foreign_key' => 'mvvfile_id',
            'assoc_func' => 'findCached',
        );

        $config['additional_fields']['range_type']['get'] = 'getRangeType';


        parent::configure($config);
    }

    /**
     * Returns the rangetype of the document based on its foldertype.
     *
     * @return bool|string Returns false on failure, otherwise the name of the range.
     */
    public function getRangeType()
    {
        return $this->range_type;
    }

    protected function logChanges($action = null)
    {
        $log_action = 'MVV_FILE_RANGE_' . mb_strtoupper($action);
        $affected = $this->mvvfile_id;
        $co_affected = $this->range_id;
        $info = ['mvv_files_ranges.*'];
        $debug_info = $this->id;
        if ($action === 'update') {
            $logged_fields = [
                'range_type',
                'position',
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
        StudipLog::log($log_action, $affected, $co_affected, implode(' | ', $info), $debug_info);
    }

}
