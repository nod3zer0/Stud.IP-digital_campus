<?php
/**
 * ExternPageCourses.php - Class to provide a list of courses as extern page.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.4
 */

class ExternPageCourses extends ExternPage
{
    /**
     * @see ExternPage::getSortFields()
     */
    public function getSortFields(): array
    {
        return [];
    }

    /**
     * @see ExternPage::getDataFields()
     */
    public function getDataFields($object_classes = []): array
    {
        return parent::getDataFields(
            [
                'sem'
            ]
        );
    }

    /**
     * @see ExternPage::getConfigFields()
     */
    public function getConfigFields($as_array = false)
    {
        $fields = '
            groupby        option,
            language       option,
            startsem       option,
            semcount       int,
            semswitch      int,
            participating  int,
            categories     optionArray,
            studyareas     optionArray,
            scope_kids     int,
            institutes     optionArray,
            searchtext,
            sublevels      int,
            semtypes       optionArray,
            escaping
        ';
        return $as_array ? self::argsToArray($fields) : $fields;
    }

    /**
     * @see ExternPage::getAllowedRequestParams()
     */
    public function getAllowedRequestParams($as_array = false)
    {
        $params = [
            'groupby',
            'language',
            'startsem',
            'semcount',
            'semswitch',
            'participating',
            'categories',
            'studyareas',
            'institutes',
            'searchtext',
            'sublevels',
            'semtypes',
         ];
        return $as_array ? $params : implode(',', $params);
    }

    /**
     * @see ExternPage::getMarkersContents()
     */
    public function getMarkersContents(): array
    {
        return $this->getContent();
    }

    /**
     * Retries all courses filtered by configuration settings and search term.
     *
     * @return SimpleCollection The found courses as course objects.
     * @throws Exception
     */
    private function getCourses(): SimpleCollection
    {
        $params = [];
        $search_sql = $this->getSearchSQL($params);
        $query = "
            SELECT
                DISTINCT `seminare`.*
            FROM
                `seminare`
                LEFT JOIN `seminar_sem_tree`
                    ON `seminare`.`Seminar_id` = `seminar_sem_tree`.`seminar_id`
                LEFT JOIN `seminar_inst`
                    ON `seminare`.`Seminar_id` = `seminar_inst`.`Seminar_id`
                LEFT JOIN `semester_courses`
                ON `semester_courses`.`course_id` = `seminare`.`Seminar_id`";
        if ($search_sql || $this->groupby === '3') {
            $query .= "
                LEFT JOIN `seminar_user` ON (`seminare`.`Seminar_id` = `seminar_user`.`seminar_id`
                    AND `seminar_user`.`status` = 'dozent')";
        }
        $query .= "
            WHERE (`semester_courses`.`semester_id` IN (:semester_ids) OR ISNULL(`semester_id`))
                AND ISNULL(`seminare`.`parent_course`) "
            . $this->getScopesSQL($params, $this->studyareas, (bool) $this->scope_kids)
            . $this->getInstitutesSQL($params)
            . $this->getSemtypesSQL($params)
            . $search_sql .
            " ORDER BY `{$this->getOrderBy()['table']}`.`{$this->getOrderBy()['field']}`" ;

        $params[':semester_ids'] = $this->getSemesters();
        return SimpleCollection::createFromArray(DBManager::get()->fetchAll(
            $query, $params, 'Course::buildExisting'));
    }

    /**
     * Returns information about possible options to order course table.
     *
     * @return array Array with order data selected in configuration.
     */
    private function getOrderBy(): array
    {
        $order_by = [
            'semnumber' => ['table' => 'seminare', 'field' => 'VeranstaltungsNummer'],
            'semname'   => ['table' => 'seminare', 'field' => 'Name'],
            'semtyp'    => ['table' => 'seminare', 'field' => 'status']
        ];
        return isset($order_by[$this->orderby]) ? $order_by[$this->orderby] : $order_by['semname'];
    }

    public function getSemesterOptions(): array
    {
         $semesters = [
             ['id' => 'previous', 'name' => _('Vorheriges')],
             ['id' => 'current',  'name' => _('Aktuelles')],
             ['id' => 'next',     'name' => _('Nächstes')]
        ];
        return array_merge($semesters, array_reverse(Semester::getAllAsArray(false)));
    }

    /**
     * Returns select-options to group the courses.
     *
     * @return array The select-options.
     */
    public function getGroupingOptions(): array
    {
        return [
            '0' => _('Keine Gruppierung'),
            '1' => _('Semester'),
            '2' => _('Studienbereiche'),
            '3' => _('Lehrende'),
            '4' => _('Veranstaltungstypen'),
            '5' => _('Einrichtungen')
        ];
    }

    /**
     * Returns associative array with content specific for each kind of grouping object.
     *
     * @param string $group_id The id of the object to group by.
     * @return array[] Array with data from grouping objects.
     * @throws InvalidArgumentException
     */
    private function getGroupingData($group_id)
    {
        $fields = [
            'NAME' => '',
            'ID'   => $group_id
        ];
        $order_by = '';
        switch ($this->groupby) {
            case 1:
                // group by semesters
                $semester = Semester::find($group_id);
                if ($semester) {
                    $fields = [
                        'NAME'        => $semester->name,
                        'SHORTNAME'   => $semester->semester_token,
                        'EXTERNAL_ID' => $semester->external_id,
                        'START'       => $semester->beginn,
                        'END'         => $semester->ende,
                        'ID'          => $group_id
                    ];
                    $order_by = date('Ymd', $semester->beginn);
                }
                break;
            case 2:
                // group by study areas
                $study_area = StudipStudyArea::find($group_id);
                if ($study_area) {
                    $fields = [
                        'NAME' => $study_area->getName(),
                        'PATH' => $study_area->getPath(),
                        'INFO' => $study_area->getInfo(),
                        'ID'   => $group_id
                    ];
                    $order_by = implode(' ', $fields['PATH']);
                }
                break;
            case 3:
                // group by users (lecturers)
                $user = User::find($group_id);
                if ($user) {
                    $fields = [
                        'NAME'       => $user->getFullName(),
                        'FIRSTNAME'  => $user->vorname,
                        'LASTNAME'   => $user->nachname,
                        'TITLEFRONT' => $user->title_front,
                        'TITLEREAR'  => $user->title_rear,
                        'ID'         => $group_id
                    ];
                    $order_by = $user->nachname . $user->vorname . $group_id;
                }
                break;
            case 4:
                // group by semtypes
                $fields = [
                    'NAME'     => $GLOBALS['SEM_TYPE'][$group_id]['name'],
                    'CATEGORY' => $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$group_id]['class']]['name'],
                    'ID'       => $group_id
                ];
                $order_by = $group_id;
                break;
            case 5:
                // group by institutes
                $institute = Institute::find($group_id);
                if ($institute) {
                    $fields = [
                        'NAME'    => $institute->name,
                        'FACULTY' => $institute->faculty->name,
                        'ID'      => $group_id
                    ];
                    $order_by = $institute->name . $group_id;
                }
                break;
            case -1:
                // not in a group
                break;
            default:
                throw new InvalidArgumentException('Invalid grouping option.');
        }
        return [
            'data'     => $fields,
            'order_by' => $order_by
        ];
    }

    /**
     * Retrieves all courses grouped and filtered by configuration settings and search term.
     *
     * @return Course[] Array of found courses grouped by selected order option.
     * @throws Exception
     */
    private function getGroupedCourses(): array
    {
        $group_table_fields = [
            '1' => '`semester_courses`.`semester_id`',
            '2' => '`seminar_sem_tree`.`sem_tree_id`',
            '3' => '`seminar_user`.`user_id`',
            '4' => '`seminare`.`status`',
            '5' => '`seminare`.`Institut_id`'
        ];
        $grouping = $group_table_fields[$this->groupby];
        $grouped_courses = [];
        $semesters = $this->getSemesters();
        $params = [
            ':semester_ids' => $semesters
        ];
        $search_sql = $this->getSearchSQL($params);
        $query = "
            SELECT DISTINCT
                IFNULL({$grouping}, '-1') AS `group_id`,
                `seminare`.`Seminar_id` AS `course_id`
            FROM
                `seminare`
                LEFT JOIN `seminar_sem_tree`
                    ON `seminare`.`Seminar_id` = `seminar_sem_tree`.`seminar_id`
                LEFT JOIN `seminar_inst`
                    ON `seminare`.`Seminar_id` = `seminar_inst`.`Seminar_id`
                LEFT JOIN `semester_courses`
                ON `semester_courses`.`course_id` = `seminare`.`Seminar_id`";
        if ($search_sql || $this->groupby === '3') {
            $query .= "
                LEFT JOIN `seminar_user` ON (`seminare`.`Seminar_id` = `seminar_user`.`seminar_id`
                    AND `seminar_user`.`status` = 'dozent')
                LEFT JOIN `auth_user_md5` USING(`user_id`)";
        }
        $query .= "
            WHERE (`semester_courses`.`semester_id` IN (:semester_ids) OR ISNULL(`semester_id`))
                AND ISNULL(`seminare`.`parent_course`) "
                . $this->getScopesSQL($params, $this->studyareas, (bool) $this->scope_kids)
                . $this->getInstitutesSQL($params)
                . $this->getSemtypesSQL($params)
                . $search_sql;

        $grouped_results = DBManager::get()->fetchGroupedPairs($query, $params);

        // handle unlimited courses
        if ($this->groupby === '1' && isset($grouped_results['-1'])) {
            foreach ($semesters as $semester_id) {
                if (isset($grouped_results[$semester_id])) {
                    $grouped_results[$semester_id] = array_merge($grouped_results[$semester_id], $grouped_results['-1']);
                } else {
                    $grouped_results[$semester_id] = $grouped_results['-1'];
                }
            }
            unset($grouped_results['-1']);
        }

        foreach ($grouped_results as $group_id => $group_result) {
            $group = $this->getGroupingData($group_id);
            $grouped_courses[$group['order_by']] = [
                'group'   => $group['data'],
                'courses' => SimpleORMapCollection::createFromArray(
                    Course::findMany($group_result)
                )->orderBy($this->getOrderBy()['field']),
            ];
        }
        ksort($grouped_courses, SORT_LOCALE_STRING);
        return $grouped_courses;
    }

    /**
     * Return sql snippet to filter courses by search term.
     *
     * @param array &$params Array with SQL parameters.
     * @return string SQL snippet or empty string if no search term is given.
     */
    private function getSearchSQL(array &$params): string
    {
        if ($this->sword && mb_strlen($this->sword) > 2) {
            $params[':sword'] = $this->sword;
            return "
                    AND (`seminare`.`Name` LIKE '%:sword%'
                        OR `seminare`.`VeranstaltungsNummer` LIKE '%:sword%'
                        OR `auth_user_md5`.`Nachname`)";
        }
        return '';
    }

    /**
     * Returns SQL snippet to filter by course types.
     *
     * @param array $params Parameters of SQL statement.
     * @return string SQL snippet or empty string if no course type is configured.
     * @throws Exception
     */
    private function getSemtypesSQL(array &$params): string
    {
        if ($this->semtypes) {
            $params[':semtypes'] = $this->semtypes;
            return ' AND `seminare`.`status` IN (:semtypes) ';
        }
        return '';
    }

    /**
     * Returns all content of courses to render the page template.
     *
     * @return array All content to render the template.
     */
    protected function getContent(): array
    {
        $i = 0;
        $count = [];
        $content = [];
        if ($this->groupby) {
            $groups_content = [];
            foreach ($this->getGroupedCourses() as $courses_group) {
                $courses_content = [];
                foreach ($courses_group['courses'] as $course) {
                    $count[$course->id] = true;
                    $courses_content[] = $this->getCourseContent($course);
                }
                $groups_content[$i]['GROUP'] = $courses_group['group'];
                $groups_content[$i++]['COURSES'] = $courses_content;
            }
            $content['GROUPED_COURSES'] = $groups_content;
            $content['GROUPED_BY']      = $this->groupby;
        } else {
            $list_content = [];
            foreach ($this->getCourses() as $course) {
                $count[$course->id] = true;
                $list_content[] = $this->getCourseContent($course);
            }
            $content['COURSES'] = $list_content;
        }
        $content['COUNT'] = count($count);
        return $content + $this->getSemesterContent();
    }

    /**
     * Returns semester content.
     *
     * @return array Content array with semester data.
     */
    private function getSemesterContent(): array
    {
        $semester_ids = $this->getSemesters();
        $semesters = [
            'END_'   => Semester::find(end($semester_ids) ?: ''),
            'START_' => Semester::find(reset($semester_ids) ?: '')
        ];
        $semester_content = [];
        array_walk($semesters,
            function ($sem, $index) use (&$semester_content) {
                if ($index) {
                    $semester_content += [
                        $index . 'SEMESTER_NAME'  => $sem->name,
                        $index . 'SEMESTER_ID'    => $sem->id,
                        $index . 'SEMESTER_START' => $sem->beginn,
                        $index . 'SEMESTER_END'   => $sem->ende
                    ];
                }
            });
        return $semester_content;
    }

    /**
     * Returns basic data of a course and of the sub-courses (recursive).
     *
     * @param Course $course The course.
     * @return array Array with basic course data.
     */
    private function getCourseContent(Course $course): array
    {
        $course_content = [
            'TITLE'           => $course->name,
            'FULLTITLE'       => $course->getFullname(),
            'SUBTITLE'        => $course->untertitel,
            'NUMBER'          => $course->veranstaltungsnummer,
            'SEMESTER'        => $course->getFullname('sem-duration-name'),
            'FORM'            => $course->art,
            'ROOM'            => $course->ort,
            'CYCLE'           => Seminar::getInstance($course->id)->getDatesExport(['show_room' => true]),
            'AVATAR_URL'      => $course->getItemAvatarURL(),
            'INFO_URL'        => $course->getItemURL(),
            'LECTURERS'       => $this->getContentMembers($course, 'dozent'),
            'SEMTYPE_NAME'    => $GLOBALS['SEM_TYPE'][$course->status]['name'],
            'SEMTYPE_NUMBER'  => $course->status,
            'SEMCLASS_NAME'   =>
                $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$course->status]['class']]['name'],
            'SEMCLASS_NUMBER' => $GLOBALS['SEM_TYPE'][$course->status]['class'],
            'ID'              => $course->id,
            'SUBCOURSES'      => $this->getSubcourses($course)
        ];
        return array_merge($course_content, $this->getDatafieldMarkers($course));
    }

    /**
     * Retrieves sub-courses of a course.
     *
     * @param Course $course The parent course.
     * @return array All direct sub-courses.
     */
    private function getSubcourses(Course $course): array
    {
        $subcourses = [];
        foreach ($course->children as $child) {
            $subcourses[] = $this->getCourseContent($child);
        }
        return $subcourses;
    }

}
