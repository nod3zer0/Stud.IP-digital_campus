<?php
/**
 * ExternPagePersBrowse.php - Class to provide lists of institute members
 * from all institutes.
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

class ExternPagePersBrowse extends ExternPage
{
    public function __construct($config)
    {
        parent::__construct($config);
        $this->institute = Institute::find($this->page_config->range_id);
    }

    /**
     * @see ExternPage::getSortFields()
     */
    public function getSortFields(): array
    {
        return [];
    }

    /**
     * @see ExternPage::getDataFields()
     
     * @param array $classes
     * @return array
     */
    public function getDataFields($object_classes = []): array
    {
        return parent::getDataFields(
            [
                'user',
                'inst'
            ]
        );
    }

    /**
     * @param false $as_array
     * @return array|mixed|string
     */
    public function getConfigFields($as_array = false)
    {
        $args = '
            language       option,
            escaping       option,
            instperms      optionArray,
            onlylecturers  int,
            institutes     optionArray
        ';
        return $as_array ? self::argsToArray($args) : $args;
    }

    public function getAllowedRequestParams($as_array = false)
    {
        $params = [
            'language',
            'initiale',
            'item_id',
        ];
        return $as_array ? $params : implode(',', $params);
    }

    public function getMarkersContents(): array
    {
        return $this->getContent();
    }

    /**
     * Returns select-options with institute permissions.
     *
     * @return array Array with select-options.
     */
    public function getInstitutePermissionOptions(): array
    {
        return [
            'tutor'  => _('Tutoren/Tutorinnen'),
            'dozent' => _('Dozenten/Dozentinnen'),
            'admin'  => _('Administratoren/Administratorinnen')
        ];
    }

    protected function getContent()
    {
        // at least one institute has to be selected in the configuration
        if (!is_array($this->institutes)) {
            return [];
        }

        $content = [
            'PERSONS'    => $this->getContentListPersons(),
            'CHARACTERS' => $this->getContentListCharacters(),
            'INSTITUTES' => $this->getContentListInstitutes(),
        ];

        return $content;
    }

    protected function getContentListPersons()
    {
        $content = [];
        if ($this->initiale) {
            if ($this->onlylecturers) {
                $current_semester = Semester::findCurrent();
                $query = "
                    SELECT
                        ui.Institut_id, su.user_id
                    FROM seminar_user su
                        LEFT JOIN auth_user_md5 aum USING(user_id)
                        LEFT JOIN seminare s USING (seminar_id)
                        LEFT JOIN user_inst ui USING(user_id)
                        LEFT JOIN semester_courses sc ON sc.course_id = su.seminar_id
                    WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER(?)
                        AND su.status = 'dozent'
                        AND s.visible = 1
                        AND (sc.semester_id = ? OR ISNULL(sc.semester_id))
                        AND ui.Institut_id IN (?)
                        AND ui.inst_perms = 'dozent'
                        AND ui.externdefault = 1
                        AND " . get_ext_vis_query();
                $params = [
                    mb_substr($this->initiale, 0, 1),
                    $current_semester->id,
                    $this->institutes,
                ];
            } else {
                // get only users with the given status
                $query = "
                    SELECT
                        ui.Institut_id, ui.user_id
                    FROM user_inst ui
                        LEFT JOIN auth_user_md5 aum USING(user_id)
                    WHERE LOWER(LEFT(TRIM(aum.Nachname), 1)) = LOWER(?)
                        AND ui.inst_perms IN(?)
                        AND ui.Institut_id IN (?)
                        AND ui.externdefault = 1
                        AND " . get_ext_vis_query();
                $params = [
                    mb_substr($this->initiale, 0, 1),
                    $this->instperms,
                    $this->institutes,
                ];
            }
        // item_id is given and it is in the list of item_ids selected in the configuration
        } else if ($this->item_id && in_array($this->item_id, $this->institutes)) {
            if ($this->onlylecturers) {
                $current_semester = Semester::findCurrent();
                // get only users with status dozent in a visible seminar in the current semester
                $query = "
                    SELECT
                        ui.Institut_id, ui.user_id
                    FROM user_inst ui
                        INNER JOIN auth_user_md5 aum USING (user_id)
                        LEFT JOIN seminar_user su USING(user_id)
                        LEFT JOIN seminare s USING (seminar_id)
                        LEFT JOIN semester_courses sc ON sc.course_id = s.seminar_id
                    WHERE ui.Institut_id = ?
                        AND ui.inst_perms = 'dozent'
                        AND ui.externdefault = 1
                        AND " . get_ext_vis_query() . "
                        AND su.status = 'dozent'
                        AND s.visible = 1
                        AND (sc.semester_id = ? OR ISNULL(sc.semester_id)";
                $params = [
                    $this->item_id,
                    $current_semester->id,
                ];
            } else {
                // get only users with the given status
                $query = '
                    SELECT
                        ui.Institut_id, ui.user_id
                    FROM user_inst ui
                        INNER JOIN auth_user_md5 aum USING (user_id)
                    WHERE ui.Institut_id = ?
                        AND ui.inst_perms IN(?)
                        AND ui.externdefault = 1
                        AND ' . get_ext_vis_query();
                $params = [
                    $this->item_id,
                    $this->instperms,
                ];
            }
        } else {
            return [];
        }
        $rows = DBManager::get()->fetchAll($query, $params);
        $user_list = [];
        foreach ($rows as $row) {
            if (!isset($user_list[$row['user_id']])) {
                $user_list[$row['user_id']] = $row['user_id'] . $row['Institut_id'];
            }
        }
        if (count($user_list) === 0) {
            return [];
        }

        $query = '
            SELECT
                ui.Institut_id, ui.raum, ui.sprechzeiten, ui.Telefon,
                inst_perms,  i.Name, aum.user_id, aum.Nachname,
                aum.Vorname
            FROM user_inst ui
                LEFT JOIN Institute i USING(Institut_id)
                LEFT JOIN auth_user_md5 aum USING(user_id)
                LEFT JOIN user_info uin USING(user_id)
            WHERE CONCAT(ui.user_id, ui.Institut_id) IN (?)
                AND ' . get_ext_vis_query() . '
            ORDER BY aum.Nachname, aum.Vorname';
        $rows = DBManager::get()->fetchAll($query, [$user_list]);

        foreach ($rows as $row) {
            $user = User::find($row['user_id']);
            $content[] = array_merge(
                [
                    'FULLNAME'    => $user->getFullName(),
                    'LASTNAME'    => $user->nachname,
                    'FIRSTNAME'   => $user->vorname,
                    'TITLEFRONT'  => $user->title_front,
                    'TITLEREAR'   => $user->title_rear,
                    'USERNAME'    => $user->username,
                    'USERID'      => $user->id,
                    'INSTNAME'    => $row['Name'],
                    'PHONE'       => $row['Telefon'],
                    'ROOM'        => $row['raum'],
                    'EMAIL'       => get_visible_email($user->id),
                    'OFFICEHOURS' => $row['sprechzeiten']
                ],
                $this->getDatafieldMarkers($user));
        }
        return $content;
    }

    /**
     * Returns an array with content to display a list of characters
     * (initials, the first character of the persons last name).
     *
     * @return array The content with a list of initials.
     */
    private function getContentListCharacters()
    {
        // at least one institute has to be selected in the configuration
        if (!is_array($this->institutes)) {
            return [];
        }

        if ($this->onlylecturers) {
            $current_semester = Semester::findCurrent();
            $query = "
                SELECT COUNT(DISTINCT aum.user_id) as count_user,
                    UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale
                FROM user_inst ui
                    LEFT JOIN seminar_user su ON ui.user_id = su.user_id
                    LEFT JOIN seminare s ON su.Seminar_id = s.Seminar_id
                    LEFT JOIN semester_courses sc ON s.seminar_id = sc.course_id
                    LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id
                WHERE su.status = 'dozent' AND s.visible = 1
                    AND (sc.semester_id = ? OR ISNULL(sc.semester_id))
                    AND TRIM(aum.Nachname) != ''
                    AND ui.Institut_id IN (?)
                    AND ui.externdefault = 1
                    AND " . get_ext_vis_query() . '
                GROUP BY initiale';
            $params = [
                $current_semester->id,
                $this->institutes,
            ];
        } else {
            $query = "
                SELECT
                    COUNT(DISTINCT ui.user_id) as count_user,
                    UPPER(LEFT(TRIM(aum.Nachname),1)) AS initiale
                FROM user_inst ui
                    LEFT JOIN auth_user_md5 aum USING (user_id)
                WHERE ui.inst_perms IN (?)
                    AND ui.Institut_id IN (?)
                    AND ui.externdefault = 1
                    AND TRIM(aum.Nachname) != ''
                GROUP BY initiale";
            $params = [
                $this->instperms,
                $this->institutes,
            ];
        }

        $rows = DBManager::get()->fetchAll($query, $params);
        $content = [];
        foreach ($rows as $row) {
            $content[] = [
                'CHARACTER_USER'  => $row['initiale'],
                'CHARACTER_COUNT' => $row['count_user'],
            ];
        }
        return $content;
    }

    /**
     * Returns an array with institute data
     *
     * @return array
     */
    private function getContentListInstitutes() {
        // at least one institute has to be selected in the configuration
        if (!is_array($this->institutes)) {
            return [];
        }

        $query = "
            SELECT
                Institute.*
            FROM
                Institute
            WHERE Institut_id IN (?)
                AND fakultaets_id != Institut_id
            ORDER BY Name ASC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->institutes]);

        $current_semester = Semester::findCurrent();
        $content = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if ($this->onlylecturers) {
                // get only users with status dozent in a visible seminar in the current semester
                $query = "
                    SELECT
                        COUNT(DISTINCT(su.user_id)) AS count_user
                    FROM
                        user_inst ui
                        LEFT JOIN seminar_user su USING(user_id)
                        LEFT JOIN seminare s USING (seminar_id)
                        LEFT JOIN semester_courses sc ON s.seminar_id = sc.course_id
                        LEFT JOIN auth_user_md5 aum ON su.user_id = aum.user_id
                    WHERE ui.Institut_id = ?
                        AND su.status = 'dozent'
                        AND ui.externdefault = 1
                        AND " . get_ext_vis_query() . "
                        AND ui.inst_perms = 'dozent'
                        AND (sc.semester_id = ? OR ISNULL(sc.semester_id))";
                $params = [
                    $row['Institut_id'],
                    $current_semester->id,
                ];
            } else {
                // get only users with the given status
                $query = "
                    SELECT
                        COUNT(DISTINCT(ui.user_id)) AS count_user
                    FROM
                        user_inst ui
                        INNER JOIN auth_user_md5 aum USING (user_id)
                    WHERE ui.Institut_id = ?
                        AND ui.inst_perms IN(?)
                        AND ui.externdefault = 1
                        AND " . get_ext_vis_query();
                $params = [
                    $row['Institut_id'],
                    $this->instperms,
                ];
            }

            $state = DBManager::get()->prepare($query);
            $state->execute($params);
            while ($row_count = $state->fetch(PDO::FETCH_ASSOC)) {
                if ($row_count['count_user'] > 0) {
                    $institute = Institute::build($row, false);
                    $content[] = array_merge(
                        [
                            'NAME'        => $institute->name,
                            'FULLNAME'    => $institute->getFullname(),
                            'ID'          => $institute->id,
                            'COUNT_USERS' => $row_count['count_user'],
                        ],
                        $this->getDatafieldMarkers($institute)
                    );
                }
            }
        }
        return $content;
    }

}
