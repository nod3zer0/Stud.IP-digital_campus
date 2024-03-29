<?php
/**
 * ModulteilLanguage.php
 * Model class for assignments of languages to Modulteile
 * (table mvv_modulteil_language)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 *
 * @property array $id alias for pk
 * @property string $modulteil_id database column
 * @property string $lang database column
 * @property string $language alias column for lang
 * @property int $position database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Modulteil $modulteil belongs_to Modulteil
 */

class ModulteilLanguage extends ModuleManagementModel
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modulteil_language';

        $config['belongs_to']['modulteil'] = [
            'class_name' => Modulteil::class,
            'foreign_key' => 'modulteil_id',
            'assoc_func' => 'findCached',
        ];

        $config['alias_fields']['language'] = 'lang';

        parent::configure($config);
    }

    /**
     * Retrieves all languages assigned to the given Modulteil.
     *
     * @see mvv_config.php for defined languages.
     * @param type $modulteil_id The id of a Modulteil.
     * @return array An array with lnguage key as key and name as value.
     */
    public static function findByModulteil($modulteil_id)
    {
        $languages = [];
        $modulteil_languages = parent::getEnrichedByQuery('
                SELECT *
                FROM mvv_modulteil_language
                WHERE modulteil_id = ?
                ORDER BY position, mkdate',
            [$modulteil_id]
        );
        foreach ($modulteil_languages as $language) {
            $languages[$language->lang] = $language;
        }
        return $languages;
    }

    public function getDisplayName()
    {
        return $GLOBALS['MVV_MODULTEIL']['SPRACHE']['values'][$this->lang]['name'];
    }

    public function validate()
    {
        $ret = parent::validate();
        $languages = $GLOBALS['MVV_MODULTEIL']['SPRACHE']['values'];
        if (!$languages[$this->lang]) {
            $ret['languages'] = true;
            $messages = [_('Unbekannte Unterrichtssprache')];
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
        return $ret;
    }

    /**
     * Inherits the status of the parent modulteil.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->modulteil) {
            return $this->modulteil->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }
}
