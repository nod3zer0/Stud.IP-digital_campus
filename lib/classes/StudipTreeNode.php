<?php

/**
 * Interface StudipTreeNode
 * An abstract representation of a tree node in Stud.IP
 *
 * @author Thomas Hackl <hackl@data-quest.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.3
 */

interface StudipTreeNode
{

    /**
     * Fetches a node by the given ID. The implementing class knows what to do.
     *
     * @param mixed $id
     * @return StudipTreeNode
     */
    public static function getNode($id): StudipTreeNode;

    /**
     * Get all direct children of the given node.
     *
     * @param bool $onlyVisible fetch only visible nodes?
     * @return StudipTreeNode[]
     */
    public function getChildNodes(bool $onlyVisible = false): array;

    /**
     * Fetches an array of all nodes the given course is assigned to.
     *
     * @param string $course_id
     * @return array
     */
    public static function getCourseNodes(string $course_id): array;

    /**
     * This node's unique ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * A name (=label) for this node.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Optional description for this node.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Gets an optional Image (Icon or Avatar) for this node.
     *
     * @return Icon|Avatar|null
     */
    public function getImage();

    /**
     * Indicator if this node has children.
     *
     * @return bool
     */
    public function hasChildNodes(): bool;

    /**
     * How many courses are assigned to this node in the given semester?
     *
     * @param string $semester_id
     * @param int $semclass
     * @param bool $with_children
     * @return int
     */
    public function countCourses(
        string $semester_id = '',
        int $semclass = 0,
        bool $with_children = false
    ): int;

    /**
     * Fetches courses assigned to this node in the given semester.
     *
     * @param string $semester_id
     * @param int $semclass
     * @param string $searchterm
     * @param bool $with_children
     * @param string[] $courses
     *
     * @return Course[]
     */
    public function getCourses(
        string $semester_id = 'all',
        int $semclass = 0,
        string $searchterm = '',
        bool $with_children = false,
        array $courses = []
    ): array;

    /**
     * Returns an array containing all ancestor nodes with id and name.
     *
     * @return array
     */
    public function getAncestors(): array;

}
