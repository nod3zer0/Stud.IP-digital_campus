<?php
/**
 * StudycourseLanguage.php
 * Model class for assignments of languages to study courses.
 * (table mvv_studycourse_language
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 *
 * @property array $id alias for pk
 * @property string $studiengang_id database column
 * @property string $lang database column
 * @property string $language alias column for lang
 * @property int $position database column
 * @property string|null $author_id database column
 * @property string|null $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Studiengang $studycourse belongs_to Studiengang
 * @property-read mixed $display_name additional field
 */

class StudycourseLanguage extends ModuleManagementModel
{
    protected static function configure($config = array())
    {
        $config['db_table'] = 'mvv_studycourse_language';

        $config['belongs_to']['studycourse'] = array(
            'class_name' => Studiengang::class,
            'foreign_key' => 'studiengang_id',
            'assoc_func' => 'findCached',
        );

        $config['alias_fields']['language'] = 'lang';
        $config['additional_fields']['display_name']['get'] = 'getDisplayName';

        parent::configure($config);
    }

    public function getDisplayName()
    {
        return $GLOBALS['MVV_STUDIENGANG']['SPRACHE']['values'][$this->lang]['name'];
    }

    public function validate()
    {
        $ret = parent::validate();
        $languages = $GLOBALS['MVV_STUDIENGANG']['SPRACHE']['values'];
        if (!$languages[$this->lang]) {
            $ret['languages'] = true;
            $messages = array(_('Unbekannte Unterrichtssprache'));
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
