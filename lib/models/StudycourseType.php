<?php
/**
 * StudycourseType.php
 * Model class for assignments of study courses to configured types.
 * (table mvv_studycourse_type)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.4
 *
 * @property array $id alias for pk
 * @property string $studiengang_id database column
 * @property string $type database column
 * @property string|null $author_id database column
 * @property string|null $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Studiengang $studycourse belongs_to Studiengang
 */

class StudycourseType extends ModuleManagementModel
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_studycourse_type';

        $config['belongs_to']['studycourse'] = array(
            'class_name' => Studiengang::class,
            'foreign_key' => 'studiengang_id',
            'assoc_func' => 'findCached',
        );

        parent::configure($config);
    }

    public function getDisplayName()
    {
        return $GLOBALS['MVV_STUDIENGANG']['STUDYCOURSE_TYPE']['values'][$this->type]['name'];
    }

    public function validate()
    {
        $ret = parent::validate();
        $types = $GLOBALS['MVV_STUDIENGANG']['STUDYCOURSE_TYPE']['values'];
        if (!$types[$this->type]) {
            $ret['types'] = true;
            $messages = array(_('Unbekannter Studiengangstyp'));
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
        return $ret;
    }

    /**
     * Inherits the status of the parent study course.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->studycourse) {
            return $this->studycourse->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_STUDIENGANG']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}
