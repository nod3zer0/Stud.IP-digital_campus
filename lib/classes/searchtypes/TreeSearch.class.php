<?php
/**
 * TreeSearch.class.php - Class of type SearchType used for searches with QuickSearch
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <hackl@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

class TreeSearch extends StandardSearch
{
    /**
     *
     * @param string $search The search type.
     *
     * @param Array $search_settings Settings for the selected seach type.
     *     Depending on the search type different settings are possible
     *     which can change the output or the display of the output
     *     of the search. The array must be an associative array
     *     with the setting as array key.
     *     The following settings are implemented:
     *     Search type 'room':
     *     - display_seats: If set to true, the seats will be displayed
     *       after the name of the room.
     *
     * @return void
     */
    public function __construct($search, $search_settings = [])
    {
        if (is_array($search_settings)) {
            $this->search_settings = $search_settings;
        }

        $this->avatarLike = $this->search = $search;
        $this->sql = $this->getSQL();
    }

    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        switch ($this->search) {
            case 'sem_tree_id':
                return _('Studienbereich suchen');
            case 'range_tree_id':
                return _('Eintrag in der Einrichtungshierarchie suchen');
            default:
                throw new UnexpectedValueException('Invalid search type {$this->search}');
        }
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL()
    {
        switch ($this->search) {
            case 'sem_tree_id':
                return "SELECT `sem_tree_id`, `name`
                        FROM `sem_tree`
                        WHERE `name` LIKE :input
                           OR `info` LIKE :input
                       ORDER BY `name`";
            case 'range_tree_id':
                return "SELECT t.`item_id`, IF(t.`studip_object_id` IS NULL, t.`name`, i.`name`)
                        FROM `range_tree` t
                        LEFT JOIN `Institute` i ON (i.`Institut_id` = t.`studip_object_id`)
                        WHERE t.`name` LIKE :input
                           OR i.`Name` LIKE :input
                        ORDER BY t.`name`, i.`Name`";
            default:
                throw new UnexpectedValueException("Invalid search type {$this->search}");
        }
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
