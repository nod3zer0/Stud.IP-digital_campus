<?php
/**
 * StudyCourse.class.php
 * model class for table studiengang
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @copyright   2013 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias column for fach_id
 * @property string $fach_id database column
 * @property string $name database column
 * @property string|null $name_kurz database column
 * @property string|null $beschreibung database column
 * @property string|null $schlagworte database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|Degree[] $degrees has_and_belongs_to_many Degree
 * @property-read mixed $count_user additional field
 */
class StudyCourse extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'fach';

        $config['has_and_belongs_to_many']['degrees'] = [
            'class_name' => Degree::class,
            'thru_table' => 'user_studiengang',
            'thru_key' => 'fach_id',
            'thru_assoc_key' => 'abschluss_id',
            'order_by' => 'GROUP BY abschluss_id ORDER BY name'
        ];

        $config['additional_fields']['count_user']['get'] = 'countUser';

        parent::configure($config);
    }

    public function countUser()
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang WHERE fach_id = ?');
        $stmt->execute([$this->id]);
        return $stmt->fetchColumn();
    }

    public function countUserByDegree($degree_id)
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang '
                . 'WHERE fach_id = ? AND abschluss_id = ?');
        $stmt->execute([$this->id, $degree_id]);
        return $stmt->fetchColumn();
    }

    public function store()
    {
        if ($this->isNew() || $this->isDirty()) {
            $this->editor_id = $GLOBALS['user']->id;
            if (!$this->getPristineValue('author_id')) {
                $this->author_id = $GLOBALS['user']->id;
            }
        }

        return parent::store();
    }

}
