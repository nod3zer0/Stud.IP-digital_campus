<?php

/**
 * RangeTreeNode.php
 * model class for table range_tree
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <hackl@data-quest.de>
 * @copyright   2022 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.3
 *
 *
 * @property string id database column
 * @property string item_id database column
 * @property string parent_id database column
 * @property int level database column
 * @property int priority database column
 * @property string name database column
 * @property string studip_object database column
 * @property string studip_object_id database column
 */
class RangeTreeNode extends SimpleORMap implements StudipTreeNode
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'range_tree';

        $config['belongs_to']['institute'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'studip_object_id',
        ];
        $config['belongs_to']['parent'] = [
            'class_name'  => RangeTreeNode::class,
            'foreign_key' => 'parent_id',
        ];
        $config['has_many']['children'] = [
            'class_name'  => RangeTreeNode::class,
            'foreign_key' => 'item_id',
            'assoc_foreign_key' => 'parent_id',
            'order_by' => 'ORDER BY priority, name',
            'on_delete' => 'delete'
        ];

        parent::configure($config);
    }

    public static function getNode($id): StudipTreeNode
    {
        if ($id === 'root') {
            return static::build([
                'id'   => 'root',
                'name' => Config::get()->UNI_NAME_CLEAN,
            ]);
        }

        return static::find($id);
    }

    public static function getCourseNodes(string $course_id): array
    {
        $nodes = [];
        foreach (Course::find($course_id)->institutes as $institute) {
            $range = self::findOneByStudip_object_id($institute->id);
            if ($range) {
                $nodes[] = $range;
            }
        }
        return $nodes;
    }

    public function getName(): string
    {
        if ($this->id === 'root') {
            return Config::get()->UNI_NAME_CLEAN;
        }

        if ($this->institute) {
           return (string) $this->institute->name;
        }

        return $this->content['name'];
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getImage()
    {
        return $this->institute ?
            Avatar::getAvatar($this->studip_object_id) :
            Icon::create('institute');
    }

    public function hasChildNodes(): bool
    {
        return count($this->children) > 0;
    }

    /**
     * @see StudipTreeNode::getChildNodes()
     */
    public function getChildNodes(bool $onlyVisible = false): array
    {
        return self::findByParent_id($this->id, "ORDER BY `priority`, `name`");
    }

    /**
     * @see StudipTreeNode::countCourses()
     */
    public function countCourses($semester_id = '', $semclass = 0, $with_children = false): int
    {
        if ($semester_id) {
            $query = "SELECT COUNT(DISTINCT i.`seminar_id`)
                      FROM `seminar_inst` i
                      JOIN `seminare` s ON (s.`Seminar_id` = i.`seminar_id`)
                      LEFT JOIN `semester_courses` sc ON (i.`seminar_id` = sc.`course_id`)
                      WHERE i.`institut_id` IN (
                          SELECT DISTINCT `studip_object_id`
                          FROM `range_tree`
                          WHERE `item_id` IN (:ids)
                      ) AND (
                          sc.`semester_id` = :semester
                          OR sc.`semester_id` IS NULL
                      )";
            $parameters = [
                'ids' => $with_children ? $this->getDescendantIds() : [$this->id],
                'semester' => $semester_id
            ];
        } else {
            $query = "SELECT COUNT(DISTINCT `seminar_id`)
                      FROM `seminar_inst` i
                      JOIN `seminare` s ON (s.`Seminar_id` = i.`seminar_id`)
                      WHERE `institut_id` IN (
                          SELECT DISTINCT `studip_object_id`
                          FROM `range_tree`
                          WHERE `item_id` IN (:ids)
                      )";
            $parameters = ['ids' => $with_children ? $this->getDescendantIds() : [$this->id]];
        }

        if (!$GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)) {
            $query .= " AND s.`visible` = 1";
        }

        if ($semclass !== 0) {
            $query .= "  AND s.`status` IN (:types)";
            $parameters['types'] = array_map(
                function ($type) {
                    return $type['id'];
                },
                array_filter(
                    SemType::getTypes(),
                    function ($t) use ($semclass) { return $t['class'] === $semclass; }
                )
            );
        }

        return !$this->institute && !$with_children ? 0 : DBManager::get()->fetchColumn($query, $parameters);
    }

    public function getCourses(
        $semester_id = 'all',
        $semclass = 0,
        $searchterm = '',
        $with_children = false,
        array $courses = []
    ): array
    {
        if ($semester_id !== 'all') {
            $query = "SELECT DISTINCT s.*
                      FROM `seminare` s
                      JOIN `seminar_inst` i ON (i.`seminar_id` = s.`Seminar_id`)
                      LEFT JOIN `semester_courses` sem ON (sem.`course_id` = s.`Seminar_id`)
                      WHERE i.`institut_id` IN (
                          SELECT DISTINCT `studip_object_id`
                          FROM `range_tree`
                          WHERE `item_id` IN (:ids)
                      ) AND (
                          sem.`semester_id` = :semester
                          OR sem.`semester_id` IS NULL
                      )";

            $parameters = [
                'ids' => $with_children ? $this->getDescendantIds() : [$this->id],
                'semester' => $semester_id
            ];
        } else {
            $query = "SELECT DISTINCT s.*
                      FROM `seminare` s
                      JOIN `seminar_inst` i ON (i.`seminar_id` = s.`Seminar_id`)
                      WHERE i.`institut_id` IN (
                          SELECT DISTINCT `studip_object_id`
                          FROM `range_tree`
                          WHERE `item_id` IN (:ids)
                      )";
            $parameters = ['ids' => $with_children ? $this->getDescendantIds() : [$this->id]];
        }

        if (!$GLOBALS['perm']->have_perm(Config::get()->SEM_VISIBILITY_PERM)) {
            $query .= " AND s.`visible` = 1";
        }

        if ($searchterm) {
            $query .= " AND s.`Name` LIKE :searchterm";
            $parameters['searchterm'] = '%' . trim($searchterm) . '%';
        }

        if ($courses) {
            $query .= " AND s.`Seminar_id` IN (:courses)";
            $parameters['courses'] = $courses;
        }

        if ($semclass !== 0) {
            $query .= "  AND s.`status` IN (:types)";
            $parameters['types'] = array_map(
                function ($type) {
                    return $type['id'];
                },
                array_filter(
                    SemType::getTypes(),
                    function ($t) use ($semclass) { return $t['class'] === $semclass; }
                )
            );
        }

        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $query .= " ORDER BY s.`start_time`, s.`VeranstaltungsNummer`, s.`Name`";
        } else {
            $query .= " ORDER BY s.`start_time`, s.`Name`";
        }

        return DBManager::get()->fetchAll($query, $parameters, 'Course::buildExisting');
    }

    public function getDescendantIds()
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids = array_merge($ids, [$child->id], $child->getDescendantIds());
        }

        return $ids;
    }

    public function getAncestors(): array
    {
        $path = [
            [
                'id' => $this->id,
                'name' => $this->getName(),
                'classname' => self::class
            ]
        ];

        if ($this->parent_id) {
            $path = array_merge($this->getNode($this->parent_id)->getAncestors(), $path);
        }

        return $path;
    }

}
