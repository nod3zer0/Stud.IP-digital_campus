<?php
/**
 * Studienbereich... TODO
 *
 * Copyright (C) 2008 - Marcus Lunzenauer <mlunzena@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @package     studip
 *
 * @author    mlunzena
 * @author    André Noack <noack@data-quest.de>
 * @copyright (c) Authors
 *
 * @property string sem_tree_id database column
 * @property string id alias column for sem_tree_id
 * @property string parent_id database column
 * @property string priority database column
 * @property string info database column
 * @property string name database column
 * @property string studip_object_id database column
 * @property string type database column
 * @property SimpleORMapCollection _children has_many StudipStudyArea
 * @property Institute institute belongs_to Institute
 * @property StudipStudyArea _parent belongs_to StudipStudyArea
 * @property SimpleORMapCollection courses has_and_belongs_to_many Course
 */

class StudipStudyArea extends SimpleORMap implements StudipTreeNode
{
    /**
     * This constant represents the key of the root area.
     */
    const ROOT = 'root';

    protected static function configure($config = [])
    {
        $config['db_table'] = 'sem_tree';
        $config['has_many']['_children'] = [
            'class_name' => StudipStudyArea::class,
            'assoc_foreign_key' => 'parent_id',
            'assoc_func' => 'findByParent',
            'on_delete' => 'delete',
            'on_store' => 'store',
        ];
        $config['has_and_belongs_to_many']['courses'] = [
            'class_name' => Course::class,
            'thru_table' => 'seminar_sem_tree',
        ];
        $config['belongs_to']['_parent'] = [
            'class_name' => StudipStudyArea::class,
            'foreign_key' => 'parent_id',
        ];
        parent::configure($config);
    }

    /**
     * This is required, if the nodes are added backwards
     */
    public $required_children = [];

    /**
     * Returns the children of the study area with the specified ID.
     */
    static function findByParent($parent_id)
    {
        return self::findByparent_id($parent_id, "ORDER BY priority,name");
    }

    /**
     * Returns the study area with the specified ID.
     */
    public static function find($id)
    {

        $result = NULL;

        if ($id === self::ROOT) {
            $result = self::getRootArea();
        }

        else {
            $result = parent::find($id);
        }

        return $result;
    }

    /**
     * Get a string representation of this study area.
     */
    public function __toString()
    {
        return $this->id;
    }


    /**
     * Get the comment of this study area.
     */
    public function getInfo()
    {
        return $this->content['info'];
    }


    /**
     * Set the comment of this study area.
     */
    public function setInfo($info)
    {
        $this->content['info'] = (string) $info;
        return $this;
    }


    /**
     * Get the display name of this study area.
     */
    public function getName(): string
    {
        return $this->content['name'];
    }

    /**
     * Set the display name of this study area.
     */
    public function setName($name)
    {
        $this->content['name'] = (string) $name;
        return $this;
    }


    /**
     * Get the parent ID of this study area.
     */
    public function getParentId()
    {
        return $this->content['parent_id'];
    }


    /**
     * Get the parent.
     */
    public function getParent()
    {
        $result = NULL;
        if ($this->getID() !== self::ROOT) {
            $result = $this->_parent;
        }
        return $result;
    }


    /**
     * Set the parent of this study area.
     */
    public function setParentId($parent_id)
    {
        $this->content['parent_id'] = (string) $parent_id;
        $this->resetRelation('parent');
        return $this;
    }

    /**
     * get the type of this study area.
     */
    public function getType()
    {
        return $this->content['type'];
    }

    /**
     * set the type of this study area.
     */
    public function setType($type)
    {
        $this->content['type'] = (int) $type;
        return $this;
    }

    /**
     * get the name of the type of this study area, see $SEM_TREE_TYPES in config.inc.php
     *
     * @return string
     */
    public function getTypeName()
    {
        if(isset($GLOBALS['SEM_TREE_TYPES'][$this->getType()]['name'])){
            return $GLOBALS['SEM_TREE_TYPES'][$this->getType()]['name'];
        } else {
            return '';
        }
    }

    /**
     * is this study area editable, see $SEM_TREE_TYPES in config.inc.php
     *
     * @return bool
     */
    public function isEditable()
    {
        if(isset($GLOBALS['SEM_TREE_TYPES'][$this->getType()]['editable'])){
            return (bool)$GLOBALS['SEM_TREE_TYPES'][$this->getType()]['editable'];
        } else {
            return false;
        }
    }

    /**
     * is this study area hidden, see $SEM_TREE_TYPES in config.inc.php
     *
     * @return bool
     */
    public function isHidden()
    {
        if (isset($GLOBALS['SEM_TREE_TYPES'][$this->getType()]['hidden'])) {
            return (bool) $GLOBALS['SEM_TREE_TYPES'][$this->getType()]['hidden'];
        } else {
            return false;
        }
    }

    /**
     * Get the path along the sem_tree to this study area.
     *
     * @param  string     optional; TODO
     *
     * @return mixed      TODO
     */
    public function getPath($separator = NULL)
    {

        $path = [];

        $area = $this;
        while ($area) {
            if ($area->getName() != '') {
                $path[] = $area->getName();
            }
            if ($area->getParentId() == self::ROOT) {
                break;
            }
            $area = $area->getParent();
        }

        $path = array_reverse($path);

        return isset($separator)
        ? join($separator, $path)
        : $path;
    }


    /**
     * Get the priority of this study area.
     */
    public function getPriority()
    {
        return $this->content['priority'];
    }


    /**
     * Set the priority of this study area.
     */
    public function setPriority($priority)
    {
        $this->content['priority'] = (int) $priority;
        return $this;
    }


    /**
     * Returns the children of this study area.
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Returns1 TRUE if the area has children.
     */
    public function hasChildren()
    {
        return sizeof($this->_children) > 0;
    }


    /**
     * Returns TRUE if this area is the root.
     */
    public function isRoot()
    {
        return $this->getId() === self::ROOT;
    }


    /**
     * Returns TRUE if this area can be select.
     */
    public function isAssignable()
    {
        $cfg = Config::GetInstance();
        $leaves_too = $cfg->getValue('SEM_TREE_ALLOW_BRANCH_ASSIGN');
        if ($leaves_too) {
            return !$this->isRoot() && !$this->isHidden();
        } else {
            return !$this->isRoot() && !$this->isHidden() && !$this->hasChildren();
        }
    }

    /**
     * is this study area considered a study modul?, see $SEM_TREE_TYPES in config.inc.php
     *
     * @return bool
     */
    public function isModule()
    {
        return isset($GLOBALS['SEM_TREE_TYPES'][$this->getType()]['is_module']);
    }

    /**
     * Get an associative array of all study areas of a course. The array
     * contains StudipStudyArea instances
     *
     * @param  id         the course's ID
     *
     * @return SimpleCollection      a SimpleORMapCollection of that course's study areas
     */
    public static function getStudyAreasForCourse($id)
    {
        $course = Course::find($id);
        return $course ? $course->study_areas : new SimpleCollection();
    }


    /**
     * Returns the not really existing root study area.
     *
     * @return object     the root study area object
     */
    public static function getRootArea()
    {
        $root = new StudipStudyArea();
        $root->setID(self::ROOT);
        $root->setName(Config::get()->UNI_NAME_CLEAN);
        return $root;
    }


    /**
     * Search for study areas whose name matches the given search term.
     *
     * @param  string     the seach term
     *
     * @return type       <description>
     */
    public static function search($searchTerm)
    {
        $query =
        "sem_tree_id IN (
        SELECT sem_tree_id FROM sem_tree st1 WHERE name LIKE :searchTerm
        UNION DISTINCT
        SELECT sem_tree_id FROM Institute i
        INNER JOIN sem_tree st2 ON st2.studip_object_id = i.Institut_id
        WHERE i.Name LIKE :searchTerm )
        ORDER BY priority";
        return self::findBySql($query, ['searchTerm' => "%$searchTerm%"]);
    }

    /**
     * Takes an array of StudyArea objects and produces the tree to the root node
     *
     * @param array $nodes All required nodes in the tree
     * @return StudipStudyArea the root node
     */
    public static function backwards($nodes)
    {
        // create the dummy root
        $root = static::getRootArea();

        $hashmap = [];

        $i = 0;

        // let the backwardssearch begin
        while ($nodes && $i < 99) {

            //clear cache
            $newNodes = [];

            //process nodes on this level
            foreach ($nodes as $node) {

                // if we know the node already place there
                if (isset($hashmap[$node->parent_id])) {
                    $cached = $hashmap[$node->parent_id];
                    $cached->required_children[$node->id] = $node;
                } else {
                    // if we have a node that is directly under root
                    if ($node->parent_id == $root->id) {
                        $root->required_children[$node->id] = $node;
                    } else {
                        // else store in hashmap and continue
                        $hashmap[$node->parent_id] = $node->_parent;
                        $node->_parent->required_children[$node->id] = $node;
                        $newNodes[$node->id] = $node->_parent;
                    }
                }
            }
            $nodes = $newNodes;
            $i++;
        }

        // plant the tree
        return $root;
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
        return Course::find($course_id)->study_areas->getArrayCopy();
    }

    public function getDescription(): string
    {
        return $this->getInfo();
    }

    /**
     * @see StudipTreeNode::getImage()
     */
    public function getImage()
    {
        return null;
    }

    public function hasChildNodes(): bool
    {
        return count($this->_children) > 0;
    }

    /**
     * @see StudipTreeNode::getChildNodes()
     */
    public function getChildNodes(bool $onlyVisible = false): array
    {
        if ($onlyVisible) {
            $visibleTypes = array_filter($GLOBALS['SEM_TREE_TYPES'], function ($t) {
                return isset($t['hidden']) ? !$t['hidden'] : true;
            });

            return static::findBySQL(
                "`parent_id` = :parent AND `type` IN (:types) ORDER BY `priority`, `name`",
                ['parent' => $this->id, 'types' => $visibleTypes]
            );
        } else {
            return static::findByParent_id($this->id, "ORDER BY `priority`, `name`");
        }
    }

    /**
     * @see StudipTreeNode::countCourses()
     */
    public function countCourses($semester_id = 'all', $semclass = 0, $with_children = false) :int
    {
        if ($semester_id !== 'all') {
            $query = "SELECT COUNT(DISTINCT t.`seminar_id`)
                      FROM `seminar_sem_tree` t
                      JOIN `seminare` s ON (s.`Seminar_id` = t.`seminar_id`)
                      LEFT JOIN `semester_courses` sc ON (t.`seminar_id` = sc.`course_id`)
                      WHERE t.`sem_tree_id` IN (:ids)
                        AND (
                          sc.`semester_id` = :semester
                          OR sc.`semester_id` IS NULL
                        )";
            $parameters = [
                'ids' => $with_children ? $this->getDescendantIds() : [$this->id],
                'semester' => $semester_id
            ];
        } else {
            $query = "SELECT COUNT(DISTINCT t.`seminar_id`)
                      FROM `seminar_sem_tree` t
                      JOIN `seminare` s ON (s.`Seminar_id` = t.`seminar_id`)
                      WHERE `sem_tree_id` IN (:ids)";
            $parameters = ['ids' => $with_children ? $this->getDescendantIds() : [$this->id]];
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

        return $this->id === 'root' && !$with_children ? 0 : DBManager::get()->fetchColumn($query, $parameters);
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
                      JOIN `seminar_sem_tree` t ON (t.`seminar_id` = s.`Seminar_id`)
                      LEFT JOIN `semester_courses` sem ON (sem.`course_id` = s.`Seminar_id`)
                      WHERE t.`sem_tree_id` IN (:ids)
                        AND (
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
                      JOIN `seminar_sem_tree` t ON (t.`seminar_id` = s.`Seminar_id`)
                      WHERE t.`sem_tree_id` IN (:ids)";
            $parameters = ['ids' => $with_children ? $this->getDescendantIds() : [$this->id]];
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

        if ($searchterm) {
            $query .= " AND s.`Name` LIKE :searchterm";
            $parameters['searchterm'] = '%' . trim($searchterm) . '%';
        }

        if ($courses) {
            $query .= " AND t.`seminar_id` IN (:courses)";
            $parameters['courses'] = $courses;
        }

        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $query .= " ORDER BY s.`start_time`, s.`VeranstaltungsNummer`, s.`Name`";
        } else {
            $query .= " ORDER BY s.`start_time`, s.`Name`";
        }

        return DBManager::get()->fetchAll($query, $parameters, 'Course::buildExisting');
    }

    public function getAncestors(): array
    {
        $path = [
            [
                'id' => $this->id,
                'name' => $this->getName(),
                'classname' => static::class
            ]
        ];

        if ($this->parent_id) {
            $path = array_merge($this->getNode($this->parent_id)->getAncestors(), $path);
        }

        return $path;
    }

    private function getDescendantIds()
    {
        $ids = [];

        foreach ($this->_children as $child) {
            $ids = array_merge($ids, [$child->id], $child->getDescendantIds());
        }

        return $ids;
    }

    /**
     * Constructs an index from the level hierarchy, This index is a number,
     * containing the "depth" level and the priority on this level. For example,
     * a node on level 2 with priority 3 will get an index of 23.
     *
     * @return int
     */
    public function getIndex()
    {
        $level = 1;
        $index = (string) $level . (string) $this->priority;
        $current = $this;

        while ($current->getParent()) {
            $current = $current->getParent();
            $index .= $level . $current->priority;
            $level++;
        }

        return $index;
    }

}
