<?php
/**
 * ExternPage.php Abstract class as blueprint for all extern page types.
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

require_once 'vendor/exTpl/Template.php';

abstract class ExternPage
{
    /**
     * @var ExternPageConfig Storage for page configuration.
     */
    public $page_config;

    /**
     * Constructor initialized with page configuration.
     *
     * @param ExternPageConfig $config
     */
    public function __construct(ExternPageConfig $config)
    {
        $this->page_config = $config;
    }

    /**
     * Returns an associative array with field names as keys and the spoken name
     * of fields as value. The output can be sorted by these values.
     *
     * @return array Array of field names as keys and spoken names as values.
     */
    abstract public function getSortFields(): array;

    /**
     * Returns an array with all content ready to feed it to the template.
     *
     * @return array Array with content of all markers used in template.
     */
    abstract public function getMarkersContents(): array;

    /**
     * Returns all markers with content from datafields related with the given object.
     * The name of the marker begins with DATFIELD_ followed by the id of the datafield.
     *
     * @param SimpleORMap $sorm Sorm-object with datafields.
     * @return array Markers for datafields.
     */
    public function getDatafieldMarkers(SimpleORMap $sorm): array
    {
        $datafield_markers = [];
        foreach ($sorm->datafields as $datafield_entry) {
            if ($datafield_entry->datafield->view_perms !== 'root' || $this->range_id === 'studip') {
                $datafield_markers['DATAFIELD_' . $datafield_entry->datafield_id] = $datafield_entry->content;
            }
        }
        return $datafield_markers;
    }

    /**
     * Returns an array or csv list of all parameter names and types used in
     * the form to configure this external page. Used by Request::extract().
     *
     * @see Request::extract()
     * @param false $as_array True to get config fields as array.
     * @return array|string An array or csv list of all field names and types.
     */
    abstract public function getConfigFields(bool $as_array = false);

    /**
     * Magic method to get a value by given name from configuration.
     *
     * @param string $field The name of the field.
     * @return mixed The value of the field.
     */
    public function __get(string $field)
    {
        return $this->getValue($field);
    }

    /**
     * Magic method to set a field in the configuration by given value.
     *
     * @param string $field The field name.
     * @param mixed $value The value.
     */
    public function __set(string $field, $value)
    {
        $this->setValue($field, $value);
    }

    /**
     * Factory to get ExternPage object from config.
     *
     * @param ExternPageConfig $config The page config.
     * @return ExternPage The module depending on config.
     * @throw InvalidArgumentException
     */
    public static function get(ExternPageConfig $config): ExternPage
    {
        if (!$config) {
            throw new InvalidArgumentException('No configuration found.');
        }
        $page_name = 'ExternPage' . $config->type;
        if (!class_exists($page_name)) {
            // lookup plugins
            $plugins = PluginEngine::getPlugins('ExternPagePlugin');
            foreach ($plugins as $plugin) {
                if ($config->type === $plugin->getExternPageName()) {
                    return $plugin->getExternPage($config);
                }
            }
            throw new InvalidArgumentException('Unknown class ' . $page_name);
        }
        return new $page_name($config);
    }

    /**
     * Convert string with allowed arguments to array with name as key and
     * type as value.
     *
     * @param $args string String with allowed arguments.
     * @return array Associative array with arguments.
     */
    protected static function argsToArray(string $args): array
    {
        $extract = [];
        $return = [];
        foreach (explode(',', $args) as $one) {
            $extract[] = array_values(array_filter(array_map('trim', explode(' ', $one))));
        }
        foreach ($extract as $one) {
            $return[$one[0]] = $one[1];
        }
        return $return;
    }

    /**
     * Returns the processed template with all data from this external page.
     *
     * @return string The processed template with content inserted.
     */
    public function toString(): string
    {
        if (!$language = $this->language) {
            $language = "de_DE";
        }
        init_i18n($language);
        $template = preg_replace(
            ['/###([\w-]+)###/', '/<!--\s+BEGIN\s+([\w-]+)\s+-->/', '/<!--\s+END\s+[\w-]+\s+-->/'],
            ['{% $1 %}', '{% foreach $1 %}', '{% endforeach %}'], $this->template);
        exTpl\Template::setTagMarkers('{%', '%}');
        try {
            $template = new exTpl\Template($template);
            $escaping_types =
                [
                    'htmlReady',
                    'xml',
                    'json'
                ];
            if (in_array($this->escaping, $escaping_types)) {
                $template->autoescape($this->escaping);
            }
            // php functions used in templates
            $functions = [
                'date'    => function($a, $b) { return date($a, $b); },
                'substr'  => function($a, $b, $c) { return mb_substr($a, $b, $c); },
                'split'   => function($a, $b) { return mb_split($a, $b); },
                'implode' => function($a, $b) { return implode($a, $b); },
                'count'   => function($a) { return count((array) $a); }
            ];
            $out = $template->render((array) $this->getMarkersContents() + $functions);
        } catch (exTpl\TemplateParserException $ex) {
            $out = Config::get()->EXTERN_PAGES_ERROR_MESSAGE . '<br>' . $ex->getMessage();
        }

        return $out;
    }

    /**
     * Get a value from the configuration of this external page.
     *
     * @param string $field The name of the field.
     * @return mixed The value of the field.
     */
    public function getValue(string $field)
    {
        $field = $field === 'id' ? 'config_id' : $field;
        if (isset($this->page_config->$field)) {
            return $this->page_config->$field;
        }
        if ($this->page_config->conf[$field] instanceof JSONArrayObject) {
            return $this->page_config->conf[$field]->getArrayCopy();
        }
        return $this->page_config->conf[$field];
    }

    /**
     * Sets a value in the config storage by given field name.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to set.
     */
    public function setValue(string $field, $value)
    {
        if ($this->page_config->isField($field)) {
            $this->page_config->setValue($field, $value);
        } else {
            $this->page_config->conf[$field] = $value;
        }
    }

    /**
     * Returns a list (as string or array) with all names of config fields
     * overridable by request parameters.
     *
     * @param false $as_array True to get request params as array.
     * @return string|string[]|void All allowed request params as array or string.
     */
    abstract public function getAllowedRequestParams(bool $as_array = false);

    /**
     * Extract the allowed request params and overwrites the
     * values from configuration.
     */
    public function setRequestParams()
    {
        $allowed_params = $this->getAllowedRequestParams(true);
        $config_fields = $this->getConfigFields(true);
        foreach ($allowed_params as $param_name) {
            $method = $config_fields[$param_name] ?: 'get';
            $param_value = Request::$method($param_name);
            if ($param_value) {
                $this->setValue($param_name, $param_value);
            }
        }
    }

    /**
     * Stores the configuration.
     *
     * @return number|boolean
     */
    public function store()
    {
        if ($this->page_config->isNew()) {
            $this->author_id = $GLOBALS['user']->id;
        }
        $this->editor_id = $GLOBALS['user']->id;
        return $this->page_config->store();
    }

    /**
     * Retrieves content of all members of given status as content array.
     *
     * @param Course $course The course.
     * @param string $status Status of membership.
     * @return array Array with content of course members.
     */
    protected function getContentMembers(Course $course, $status): array
    {
        $content = [];
        $members = $course->getMembersWithStatus($status);
        foreach ($members as $member) {
            $user = $member->user;
            $content[] = array_merge(
                [
                    'FULLNAME'   => $user->getFullname(),
                    'LASTNAME'   => $user->nachname,
                    'FIRSTNAME'  => $user->vorname,
                    'TITLEFRONT' => $user->title_front,
                    'TITLEREAR'  => $user->title_rear,
                    'EMAIL'      => $user->email,
                    'USERNAME'   => $user->username,
                    'ID'         => $user->id,
                ],
                $this->getDatafieldMarkers($member),
                $this->getDatafieldMarkers($user));
        }
        return $content;
    }

    /**
     * Returns an array of semester ids depending on config value.
     *
     * @return array Array of semester ids.
     */
    protected function getSemesters(): array
    {
        static $semesters = [];

        if (count($semesters) > 0) {
            return $semesters;
        }
        $sem_time = date_create('now + ' . $this->semswitch . ' weeks');
        switch ($this->startsem) {
            case 'next':
                $start_semester = Semester::findNext($sem_time->getTimestamp());
                break;
            case 'current':
                $start_semester = Semester::findCurrent();
                break;
            case 'previous':
                $start_semester = Semester::findPrevious($sem_time->getTimestamp());
                break;
            default:
                $start_semester = Semester::find($this->startsem) ?: Semester::findCurrent();
        }

        $semesters = SimpleORMapCollection::createFromArray(
            Semester::findBySQL('`beginn` >= :sem_start AND `visible` = 1 ORDER BY `beginn` LIMIT :sem_count',
                [
                    'sem_start'  => $start_semester->beginn,
                    'sem_count'  => $this->semcount ?: 1
                ]
            )
        )->pluck('id');
        return $semesters;
    }

    /**
     * Returns SQL snippet to filter by study areas.
     *
     * @param array $params Array with query parameters.
     * @param array $scopes Array with selected scopes.
     * @param bool $with_kids Returns all descendent ids of the given scopes.
     * @return string SQL snippet or empty string if no study area is configured.
     * @throws Exception
     */
    protected function getScopesSQL(
        array &$params,
        array $scopes,
        bool $with_kids
    ): string {
        if (count($scopes) > 0) {
            $study_areas = StudipStudyArea::findMany($scopes);
            $scopes = [];
            if ($with_kids) {
                foreach ($study_areas as $study_area) {
                    $scopes = array_merge($scopes, [$study_area->id], $study_area->getDescendantIds());
                }
            } else {
                $scopes = SimpleORMapCollection::createFromArray($study_areas)->pluck('id');
            }
            $params[':sem_tree_ids'] = $scopes;
            return ' AND `seminar_sem_tree`.`sem_tree_id` IN (:sem_tree_ids) ';
        }
        return '';
    }

    /**
     * Returns SQL snippet to filter seminars by institutes if institute ids are stored in configuration.
     * If no institutes stored in configuration it returns an empty string.
     *
     * @param array $params Parameters of SQL statement.
     * @return string SQL snippet or empty string if no institute is configured.
     */
    protected function getInstitutesSQL(array &$params): string
    {
        $config = $this->page_config->getPristineValue('conf');
        if ($config['institutes'] instanceof JSONArrayObject) {
            $config_institutes = $config['institutes']->getArrayCopy();
        } else {
            return '';
        }
        if ($this->range_id !== 'studip') {
            $institutes = [$this->range->id];
            if ($this->range->is_fak && count($this->range->sub_institutes) > 0) {
                $institutes += $this->range->sub_institutes->pluck('id');
                if (count($config_institutes) > 0) {
                    $institutes = array_intersect(
                        $institutes,
                        $config_institutes
                    );
                }
            }
        } else {
            $institutes = $config_institutes;
        }
        // id from request parameter
        // only accepts institutes defined in config
        if (count($this->institutes) > 0) {
            $requested_institutes = array_intersect(
                $institutes,
                $this->institutes
            );
            if (count($requested_institutes) > 0) {
                $institutes = $requested_institutes;
            }
        }
        $params[':institute_ids'] = $institutes;
        $sql = ' AND (`seminare`.`Institut_id` IN (:institute_ids)';
        if ($this->participating) {
            $sql .= ' OR `seminar_inst`.`institut_id` IN (:institute_ids)';
        }
        return $sql . ') ';
    }

    /**
     * Returns an array of institutes names as values and their IDs as keys.
     *
     * @throws UnexpectedValueException;
     * @return array|false
     */
    public function getInstitutes()
    {
        if ($this->page_config->range_id === 'studip') {
            return Institute::getMyInstitutes();
        }
        $count = Institute::countBySQL("`Institut_id` = ?", [$this->page_config->range_id]);
        if ($count === 0) {
            throw new UnexpectedValueException('Unknown institute');
        }
        return DBManager::get()->fetchAll("
            SELECT `fakultaet`.`Institut_id`, `fakultaet`.`Name`, IF(`fakultaet`.`Institut_id` = `Institute`.`fakultaets_id`, 1, 0) AS `is_fak`
            FROM `Institute`
            LEFT JOIN `Institute` as `fakultaet` ON (`Institute`.`Institut_id` = `fakultaet`.`fakultaets_id`)
            WHERE `Institute`.`Institut_id` = ?
            ORDER BY fakultaet.Name ASC, is_fak DESC, Institute.Name ASC",
            [$this->page_config->range_id]
         );
    }

    /**
     * Returns an associative array with area ids as keys and the area paths up
     * to tree root as value.
     *
     * @param string $delimiter Delimiter between area names in path.
     * @return array Array with area id as index and paths as value.
     * @throws Exception
     */
    public function getStudyAreaPaths(string $delimiter = ' > '): array
    {
        $paths = [];
        StudipStudyArea::findAndMapMany(
            function ($area) use (&$paths, $delimiter) {
                $paths[$area->id] = $area->getPath($delimiter);
            },
            $this->studyareas
        );
         asort($paths, SORT_LOCALE_STRING);
         return $paths;
    }

    /**
     * Returns all datafields for the given datafield classes.
     *
     * @param array $object_classes Array of datafield class names.
     * @return DataField[] All datafields for given classes.
     */
    public function getDataFields(array $object_classes = []): array
    {
        $all_classes = DataField::getDataClass();
        $data_fields = [];
        foreach ($object_classes as $class) {
            if (isset($all_classes[$class])) {
                $data_fields[$class] = DataField::findBySQL('`object_type` = ? ORDER BY `priority`', [$class]);
            }
        }
        return $data_fields;
    }

    /**
     * Returns types of courses grouped by classes.
     *
     * @return array The grouped types.
     */
    public static function getGroupedSemTypes(): array
    {
        $grouped_sem_types = [];
        foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }
            $grouped_sem_types[$class_id] = [
                'name' => $class['name'],
                'types' => $class->getSemTypes()
            ];
        }
        return $grouped_sem_types;
    }

}
