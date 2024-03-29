<?php
/**
 * ModulLanguage.php
 * Model class for assignments of languages to modules
 * (table mvv_modul_language
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
 * @property string $modul_id database column
 * @property string $lang database column
 * @property string $language alias column for lang
 * @property int $position database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Modul $modul belongs_to Modul
 */

class ModulLanguage extends ModuleManagementModel
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_modul_language';

        $config['belongs_to']['modul'] = [
            'class_name' => Modul::class,
            'foreign_key' => 'modul_id',
            'assoc_func' => 'findCached',
        ];

        $config['alias_fields']['language'] = 'lang';

        parent::configure($config);
    }

    /**
     * Retrieves all languages assigned to the given module.
     *
     * @param string $modul_id The id of a module.
     * @return SimpleORMapCollection Collection of language assignments.
     */
    public static function findByModul($modul_id)
    {
        $languages = [];
        $module_languages = parent::getEnrichedByQuery('
            SELECT *
            FROM mvv_modul_language
            WHERE modul_id = ?
            ORDER BY position, mkdate ',
            [$modul_id]
        );
        foreach ($module_languages as $language) {
            $languages[$language->lang] = $language;
        }
        return $languages;
    }

    public function getDisplayName()
    {
        return $GLOBALS['MVV_MODUL']['SPRACHE']['values'][$this->lang]['name'];
    }

    public function validate()
    {
        $ret = parent::validate();
        $languages = $GLOBALS['MVV_MODUL']['SPRACHE']['values'];
        if (!$languages[$this->lang]) {
            $ret['languages'] = true;
            $messages = [_('Unbekannte Unterrichtssprache')];
            throw new InvalidValuesException(join("\n", $messages), $ret);
        }
        return $ret;
    }

    /**
     * Inherits the status of the parent module.
     *
     * @return string The status (see mvv_config.php)
     */
    public function getStatus()
    {
        if ($this->modul) {
            return $this->modul->getStatus();
        }
        if ($this->isNew()) {
            return $GLOBALS['MVV_MODUL']['STATUS']['default'];
        }
        return parent::getStatus();
    }

}
