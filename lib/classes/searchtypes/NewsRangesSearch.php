<?php
class NewsRangesSearch extends SearchType
{
    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        return _('Bereich suchen');
    }

    /**
     * returns the results of a search
     *
     * @param string $input the search-word(s)
     * @param array $contextual_data unused
     * @param int $limit maximum number of results (default: all)
     * @param int $offset return results starting from this row (default: 0)
     *
     * @return array  array(array(), ...)
     */
    public function getResults($input, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0)
    {
        $sql_searches = [];
        $parameters = [':input' => "%{$input}%"];

        $user = \User::findCurrent();

        // Courses
        $number_sql = Config::get()->IMPORTANT_SEMNUMBER ? "CONCAT(IFNULL(s.VeranstaltungsNummer, ''), ' '), " : '';
        $semester_sql = " CONCAT(' (',IFNULL(GROUP_CONCAT(DISTINCT semester_data.name ORDER BY semester_data.beginn SEPARATOR '-'),'" . _('unbegrenzt') . "'),')')";

        if ($GLOBALS['perm']->have_perm('root')) {
            $sql_searches[] = "SELECT *
                               FROM (
                                   SELECT CONCAT(s.`Seminar_id`, '__seminar') AS `range_id`,
                                          TRIM(CONCAT({$number_sql} s.`name`, {$semester_sql})) AS `name`
                                   FROM `seminare` AS s
                                   LEFT JOIN `semester_courses` AS sc ON s.`Seminar_id` = sc.`course_id`
                                   LEFT JOIN `semester_data` USING (`semester_id`)
                                   WHERE s.`name` LIKE :input
                                   GROUP BY s.`Seminar_id`
                                   ORDER BY s.`start_time` DESC
                               ) AS course_select";
        } elseif ($GLOBALS['perm']->have_perm('admin')) {
            $sem_inst = Config::get()->ALLOW_ADMIN_RELATED_INST ? 'si' : 's';

            $sql_searches[] = "SELECT *
                               FROM (
                                   SELECT CONCAT(s.`Seminar_id`, '__seminar') AS `range_id`,
                                          TRIM(CONCAT({$number_sql} s.`name`, {$semester_sql})) AS `name`
                                   FROM `seminare` AS s
                                   JOIN `seminar_inst` si USING (Seminar_id)
                                   LEFT JOIN `semester_courses` AS sc ON s.`Seminar_id` = sc.`course_id`
                                   LEFT JOIN `semester_data` USING (`semester_id`)
                                   WHERE {$sem_inst}.`institut_id` IN (:institutes)
                                     AND `name` LIKE :input
                                   GROUP BY s.`Seminar_id`
                                   ORDER BY s.`start_time` DESC
                               ) AS course_select";

            $parameters[':institutes'] = $this->getAdminInstitutes($user);
        } else {
            $sql_searches[] = "SELECT *
                               FROM (
                                   SELECT CONCAT(s.`Seminar_id`, '__seminar') AS `range_id`,
                                          TRIM(CONCAT({$number_sql} s.`name`, {$semester_sql})) AS `name`
                                   FROM `seminare` AS s
                                   JOIN `seminar_user` USING (`Seminar_id`) AS su
                                   LEFT JOIN `semester_courses` AS sc ON s.`Seminar_id` = sc.`course_id`
                                   LEFT JOIN `semester_data` USING (`semester_id`)
                                   WHERE s.`name` LIKE :input
                                     AND su.`user_id` = :user_id
                                     AND su.`status` IN ('tutor', 'dozent')
                                   GROUP BY s.`Seminar_id`
                                   ORDER BY s.`start_time` DESC
                               ) AS course_select ";
            $parameters[':user_id'] = $user->id;
        }

        // Institutes
        if ($GLOBALS['perm']->have_perm('root')) {
            $sql_searches[] = "SELECT CONCAT(`Institut_id`, '__institute') AS `range_id`, `Name` AS `name`
                               FROM `Institute`
                               WHERE `name` LIKE :input";
        } else {
            $sql_searches[] = "SELECT CONCAT(`Institut_id`, '__institute') AS `range_id`, `Name` AS `name`
                               FROM `Institute`
                               JOIN `user_inst` USING (`Institut_id`)
                               WHERE `user_inst`.`user_id` = :user_id
                                 AND `user_inst`.`inst_perms` IN ('tutor', 'dozent', 'admin')
                                 AND `name` LIKE :input";
            $parameters[':user_id'] = $user->id;
        }

        // Other (start page for root) and personal pages (only own profile for everyone except root)
        if ($GLOBALS['perm']->have_perm('root')) {
            $sql_searches[] = "SELECT *
                               FROM (
                                  SELECT CAST('studip__home' AS BINARY) AS `range_id`, :home_label AS `name`
                               ) AS tmp_global_table
                               WHERE `name` LIKE :input";
            $parameters[':home_label'] = _('Stud.IP-Startseite');

            $sql_searches[] = "SELECT CONCAT(`user_id`, '__person') AS `range_id`, CONCAT(`Vorname`, ' ', `Nachname`) AS `name`
                               FROM `auth_user_md5`
                               WHERE CONCAT(`Vorname`, ' ', `Nachname`) LIKE :input";
        } elseif ($GLOBALS['perm']->have_perm('admin')) {
            $sql_searches[] = "SELECT CONCAT(`user_id`, '__person') AS `range_id`, CONCAT(`Vorname`, ' ', `Nachname`) AS `name`
                               FROM `auth_user_md5` AS aum
                               JOIN `user_inst` AS ui USING (`user_id`)
                               WHERE ui.`institut_id` IN (:institutes)
                                 AND CONCAT(`Vorname`, ' ', `Nachname`) LIKE :input";

            $parameters[':institutes'] = $this->getAdminInstitutes($user);
        } else {
            $sql_searches[] = "SELECT *
                               FROM (
                                 SELECT CAST(CONCAT(:user_id, '__person') AS BINARY) AS `range_id`,
                                        CONCAT_WS(' - ', :user_name, :profile_name) AS `name`
                               ) AS tmp_user_table
                               WHERE `name` LIKE :input";
            $parameters[':user_id'] = $user->id;
            $parameters[':user_name'] = $user->getFullname();
            $parameters[':profile_name'] = _('Profilseite');
        }

        $searches = implode(' UNION ALL ', $sql_searches);

        $query = "SELECT * FROM ({$searches}) AS tmp";

        if ($offset || $limit != PHP_INT_MAX) {
            $query .= " LIMIT {$offset}, {$limit}";
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Returns an adress of the avatar of the searched item (if avatar enabled)
     *
     * @param string $id id of the item which can be username, user_id, Seminar_id or Institut_id
     * @return string url to the avatar image
     */
    public function getAvatar($id)
    {
        $avatar = $this->getAvatarObject($id);
        return $avatar ? $avatar->getURL(Avatar::MEDIUM): '';
    }

    /**
     * Returns an html tag of the image of the searched item (if avatar enabled)
     *
     * @param string $id id of the item which can be username, user_id, Seminar_id or Institut_id
     * @param string $size enum(NORMAL, SMALL, MEDIUM): size of the avatar
     * @param array $options
     * @return string like "<img src="...avatar.jpg" ... >"
     */
    public function getAvatarImageTag($id, $size = Avatar::SMALL, $options = [])
    {
        $avatar = $this->getAvatarObject($id);
        return $avatar ? $avatar->getImageTag($size, $options): '';
    }

    /**
     * Returns an avatar object for the given combined id.
     *
     * @param string $id
     * @return Avatar|null
     */
    protected function getAvatarObject(string $id): ?Avatar
    {
        [$id, $type] = explode('__', $id);

        switch ($type) {
            case 'person':
                return Avatar::getAvatar($id);
            case 'seminar':
                return CourseAvatar::getAvatar($id);
            case 'institute':
                return InstituteAvatar::getAvatar($id);
            default:
                return null;
        }
    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return string path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }

    /**
     * Returns a list of all institute ids the given user is admin for.
     *
     * @param User $user
     * @return string[]
     */
    protected function getAdminInstitutes(User $user): array
    {
        $query = "SELECT DISTINCT i.`Institut_id`
                  FROM `user_inst` AS ui
                  JOIN `Institute` AS i 
                    ON ui.`Institut_id` IN (i.`Institut_id`, i.`fakultaets_id`)   
                  WHERE ui.`user_id` = :user_id
                    AND ui.`inst_perms` = 'admin'";
        return DBManager::get()->fetchFirst($query, [
            ':user_id' => $user->id,
        ]);
    }
}
