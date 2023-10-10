<?php
/**
 * This plugin interface is used to add functionality to the sidebar of the
 * admin courses page.
 *
 * @see    AdminCourseOptionsWidget
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @since Stud.IP 5.4
 */
interface AdminCourseWidgetPlugin
{
    /**
     * Returns a list of widgets for the admin courses page.
     *
     * @return AdminCourseOptionsWidget[]
     */
    public function getWidgets(): iterable;

    /**
     * Return the filter values this widget provides. Return an associative
     * array with filter names as indices and filter values as values.
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Apply the set filters to the AdminCourseFilter query.
     *
     * @param AdminCourseFilter $filter
     */
    public function applyFilters(AdminCourseFilter $filter): void;


    /**
     * Set filters from the admin course page. You will be given an associative
     * array according to getFilters().
     *
     * @param array $filters
     */
    public function setFilters(array $filters): void;
}
