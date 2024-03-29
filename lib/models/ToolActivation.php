<?php
/**
 * ToolActivation.class.php
 * model class for table tools_activated
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2021 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property array $id alias for pk
 * @property string $range_id database column
 * @property string $range_type database column
 * @property int $plugin_id database column
 * @property int $position database column
 * @property JSONArrayObject|null $metadata database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Institute $institute belongs_to Institute
 * @property Course $course belongs_to Course
 */
class ToolActivation extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'tools_activated';

        $config['belongs_to']['institute'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id',
        ];

        $config['serialized_fields']['metadata'] = JSONArrayObject::class;

        $config['registered_callbacks']['before_create'][] = 'setMaxPosition';

        parent::configure($config);
    }

    public function setSerializedValue($field, $value)
    {
        if ($value === null) {
            $value = [];
        }
        return parent::setSerializedValue($field, $value);
    }

    public function getStudipModule()
    {
        return PluginManager::getInstance()->getPluginById($this->plugin_id);
    }

    public function setStudipModule(StudipModule $module)
    {
        $this->plugin_id = $module->getPluginId();
    }

    public function setMaxPosition()
    {
        $max_position = DBManager::get()->fetchColumn(
            "SELECT MAX(position) FROM tools_activated WHERE range_id = ?",
            [$this->range_id]
        );
        $this->position = $max_position === null ? 0 : $max_position + 1;
    }

    public function getDisplayname()
    {
        if (isset($this->metadata['displayname'])) {
            return $this->metadata['displayname'];
        }
        return $this->getDefaultDisplayname();
    }

    public function getDefaultDisplayname()
    {
        $module = $this->getStudipModule();
        if ($module) {
            $metadata = $module->getMetadata();
            return $metadata['displayname'] ?? $module->getPluginName();
        }
    }

    public function getVisibilityPermission()
    {
        if ($this->metadata['visibility'] === 'tutor') {
            return 'tutor';
        } else {
            return 'nobody';
        }
    }
}
