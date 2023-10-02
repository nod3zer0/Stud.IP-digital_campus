<?php
/**
 * Degree.class.php
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
 * @property string $id alias column for abschluss_id
 * @property string $abschluss_id database column
 * @property string $name database column
 * @property string|null $name_kurz database column
 * @property string|null $beschreibung database column
 * @property string $author_id database column
 * @property string $editor_id database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property SimpleORMapCollection|StudyCourse[] $professions has_and_belongs_to_many StudyCourse
 * @property-read mixed $count_user additional field
 */
class Degree extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'abschluss';

        $config['has_and_belongs_to_many']['professions'] = [
            'class_name' => StudyCourse::class,
            'thru_table' => 'user_studiengang',
            'thru_key' => 'abschluss_id',
            'thru_assoc_key' => 'fach_id',
            'order_by' => 'GROUP BY fach_id ORDER BY name'
        ];

        $config['additional_fields']['count_user']['get'] = 'countUser';
        $config['registered_callbacks']['before_store'][] = "cbUpdateAuthorId";
        parent::configure($config);
    }

    public function countUser()
    {
        $sql = 'SELECT COUNT(DISTINCT `user_id`) FROM `user_studiengang`';
        $parameters = [':degree_id' => $this->id];
        if (!$GLOBALS['perm']->have_perm('root')) {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            $sql .=  'JOIN `mvv_fach_inst` as `fach_inst` ON (`user_studiengang`.`fach_id` = `fach_inst`.`fach_id`)
                WHERE `user_studiengang`.`abschluss_id` = :degree_id AND `fach_inst`.`institut_id` IN (:inst_ids)';
            $parameters[':inst_ids'] = $inst_ids;
        } else {
            $sql .= ' WHERE `user_studiengang`.`abschluss_id` = :degree_id';
        }

        return DBManager::get()->fetchColumn($sql, $parameters);
    }

    public function countUserByStudycourse($studycourse_id)
    {
        $stmt = DBManager::get()->prepare('SELECT COUNT(DISTINCT user_id) '
                . 'FROM user_studiengang '
                . 'WHERE fach_id = ? AND abschluss_id = ?');
        $stmt->execute([$studycourse_id, $this->id]);
        return $stmt->fetchColumn();
    }

    public function cbUpdateAuthorId()
    {
        if ($this->isNew() || $this->isDirty()) {
            $this->editor_id = $GLOBALS['user']->id;
            if (!$this->getPristineValue('author_id')) {
                $this->author_id = $GLOBALS['user']->id;
            }
        }
    }
}
