<?php
/**
 * I18NStringDatafield.php
 * Class to handle i18n content of datafields.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 *
 */

class I18NStringDatafield extends I18NString
{

    /**
     * Sets the metadata (database info object_id) of this i18n datafield.
     *
     * @param array $metadata Database info for object_id.
     */
    public function setMetadata($metadata)
    {
        if (is_null($metadata['table'])) {
            $this->metadata = $metadata;
        }
    }

    /**
     * Return an array containg the text in all additional languages.
     *
     * @return array
     */
    public function toArray()
    {
        if (is_null($this->lang)) {
            $object_id = $this->metadata['object_id'];
            $this->lang = self::fetchDataForField($object_id, null, null);
        }
        return $this->lang;
    }

    /**
     * Stores the i18n String manually in the database
     *
     */
    public function storeTranslations()
    {
        if (is_array($this->lang)) {
            $object_id = $this->metadata['object_id'];
            /* Replace translations */
            DatafieldEntryModel::deleteBySQL(
                "`datafield_id` = ? AND `range_id` = ? AND `sec_range_id` = ? AND `lang` <> ''",
                [$object_id[0], $object_id[1], $object_id[2]]
            );
            foreach ($this->lang as $lang => $value) {
                if (strlen($value)) {
                    DatafieldEntryModel::create(
                        [
                            'datafield_id' => $object_id[0],
                            'range_id' => $object_id[1],
                            'sec_range_id' => $object_id[2],
                            'content' => (string) $value,
                            'lang' => (string) $lang,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Returns an I18NString object by given object_id, table and field.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @param string $field The name of the i18n field.
     * @param string $base Sets the original value or retrieve it from database
     * if null.
     * @return I18NString The I18NString object.
     */
    public static function load($object_id, $table = '', $field = '', $base = null)
    {
        if (is_null($base)) {
            $df = DatafieldEntryModel::findOneBySQL(
                "`datafield_id` = ? AND `range_id` = ? AND `sec_range_id` = ? AND `lang` = ''",
                $object_id
            );
            $base = $df->content ?? '';
        }
        $table = null;
        $field = null;
        return new self($base, self::fetchDataForField($object_id, $table, $field),
                compact('object_id', 'table', 'field'));
    }

    /**
     * Retrieves all translations for one field.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @param string $field The name oof the i18n field.
     * @return array An array with language as key and translation as value.
     */
    public static function fetchDataForField($object_id, $table, $field)
    {
        $result = [];
        
        DatafieldEntryModel::findEachBySQL(
            function (DatafieldEntryModel $model) use (&$result) {
                $result[$model->lang] = $model->content;
           },
            "`datafield_id` = ? AND `range_id` = ? AND `sec_range_id` = ? AND `lang` <> ''",
            $object_id
        );

        return $result;
    }

    /**
     * This function is not used in the context of datafields, so it always
     * returns an empty array.
     *
     * @param string $object_id The id of the object with i18n fields.
     * @param string $table The name of the table with the original values.
     * @return array An empty array.
     */
    public static function fetchDataForRow($object_id, $table)
    {
        return [];
    }

    /**
     * Removes all translations by given object id and table name. Accepts the
     * language as third parameter to remove only translations to this language.
     *
     * @param string $object_id The id of the sorm object.
     * @param string $table The table name.
     * @param string $lang Optional name of language.
     * @return int The number of deleted translations.
     */
    public static function removeAllTranslations($object_id, $table, $lang = null)
    {
        $db = DBManager::get();
        if ($lang) {
            return $db->execute('DELETE FROM `datafield_entries` '
                    . 'WHERE `datafield_id` = ? '
                    . 'AND `range_id` = ? '
                    . 'AND `sec_range_id` = ? '
                    . 'AND `lang` = ?',
                    [$object_id[0], $object_id[1], $object_id[2], $lang]);
        }
        return $db->execute('DELETE FROM `datafield_entries` '
                . 'WHERE `datafield_id` = ? '
                . 'AND `range_id` = ? '
                . 'AND `sec_range_id` = ? '
                . 'AND `table` = ?',
                $object_id);
    }

}
