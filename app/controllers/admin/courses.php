<?php
/**
 * courses.php - Controller for admin and seminar related
 * pages under "Meine Veranstaltungen"
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @author      David Siegfried <david@ds-labs.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       3.1
 */
require_once 'lib/meine_seminare_func.inc.php';
require_once 'lib/object.inc.php';
require_once 'lib/archiv.inc.php'; //for lastActivity in getCourses() method

class Admin_CoursesController extends AuthenticatedController
{

    /**
     * This method returns the appropriate widget for the given datafield.
     *
     * @param DataField datafield The datafield whose widget is requested.
     *
     * @return SidebarWidget|null Returns a SidebarWidget derivative or null in case of an error.
     */
    private function getDatafieldWidget(DataField $datafield)
    {
        if ($datafield->accessAllowed()) {
            //The current user is allowed to see this datafield.
            //Now we must distinguish between the different types of data fields:

            $datafields_filters = $GLOBALS['user']->cfg->ADMIN_COURSES_DATAFIELDS_FILTERS;

            $type = $datafield->type;

            if ($type == 'bool') {
                //bool fields just need a checkbox for the states TRUE and FALSE
                $checkboxWidget = new OptionsWidget($datafield->name);
                $checkboxWidget->addCheckbox(
                    _('Feld gesetzt'),
                    Request::bool('df_'.$datafield->id, $datafields_filters[$datafield->id] ?? false),
                    URLHelper::getURL(
                        'dispatch.php/admin/courses/index',
                        ['df_'.$datafield->id => '1']
                    ),
                    URLHelper::getURL(
                        'dispatch.php/admin/courses/index'
                    ),
                    ['onclick' => "$(this).toggleClass(['options-checked', 'options-unchecked']); STUDIP.AdminCourses.App.changeFilter({'df_".$datafield->id."': $(this).hasClass('options-checked') ? 1 : 0}); return false;"]
                );
                return $checkboxWidget;
            } elseif ($type == 'selectbox' || $type == 'radio' || $type == 'selectboxmultiple') {
                $options = array_map('trim', explode("\n", DBManager::get()->fetchColumn(
                    'SELECT typeparam FROM datafields WHERE datafield_id = ?',
                    [$datafield->id]
                )));

                if ($options) {
                    $selectWidget = new SelectWidget(
                        $datafield->name,
                        '?',
                        'df_' . $datafield->id
                    );
                    $selectWidget->addElement(
                        new SelectElement(
                            '',
                            '(' . _('keine Auswahl') . ')'
                        )
                    );
                    foreach ($options as $option) {
                        $selectWidget->addElement(
                            new SelectElement(
                                $option,
                                $option,
                                Request::get('df_'.$datafield->id, $datafields_filters[$datafield->id] ?? null) === $option
                            )
                        );
                    }
                    $selectWidget->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({'df_".$datafield->id."': $(this).find('select').val()}); return false;");
                    return $selectWidget;
                }
                return null;
            } else {
                //all other fields get a text field
                $textWidget = new SearchWidget();
                $textWidget->setTitle($datafield->name);
                $textWidget->addNeedle(
                    '',
                    'df_'.$datafield->id,
                    false,
                    null,
                    null,
                    $datafields_filters[$datafield->id] ?? null
                );
                $textWidget->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({'df_".$datafield->id."': $(this).find('input').val()}); return false;");
                return $textWidget;
            }
        }
    }

    /**
     * This method is responsible for building the sidebar.
     *
     * Depending on the sidebar elements the user has selected some of those
     * elements are shown or not. To find out what elements
     * the user has selected the user configuration is accessed.
     *
     * @param string courseTypeFilterConfig The selected value for the course type filter field, defaults to null.
     * @return null This method does not return any value.
     */
    private function buildSidebar()
    {
        /*
            Depending on the elements the user has selected
            some of the following elements may not be presented
            in the sidebar.
        */
        $visibleElements = $this->getActiveElements();

        $this->sem_create_perm = in_array(Config::get()->SEM_CREATE_PERM, ['root', 'admin', 'dozent'])
            ? Config::get()->SEM_CREATE_PERM
            : 'dozent';

        $sidebar = Sidebar::get();

        /*
            Order of elements:
            * Navigation
            * selected filters (configurable)
            * selected actions widget
            * actions
            * view filter (configurable)
            * export
        */

        /*
            Now draw the configurable elements according
            to the values inside the visibleElements array.
        */
        if (!empty($visibleElements['search'])) {
            $this->setSearchWiget();
        }
        if (!empty($visibleElements['institute'])) {
            $this->setInstSelector();
        }
        if (!empty($visibleElements['semester'])) {
            $this->setSemesterSelector();
        }
        if (!empty($visibleElements['stgteil'])) {
            Sidebar::Get()->addWidget($this->getStgteilSelector(), 'filter_stgteil');
        }
        if (!empty($visibleElements['courseType'])) {
            $this->setCourseTypeWidget();
        }
        if (!empty($visibleElements['teacher'])) {
            Sidebar::Get()->addWidget($this->getTeacherWidget(), 'filter_teacher');
        }

        //if there are datafields in the list, draw their input fields, too:
        if (!empty($visibleElements['datafields'])) {
            //The datafields entry contains an array with datafield-IDs.
            //We must fetch them from the database and show an appropriate widget
            //for each datafield.

            $visibleDatafieldIds = $visibleElements['datafields'];

            $datafields = DataField::getDataFields('sem');

            if ($datafields) {
                foreach ($datafields as $datafield) {
                    if (in_array($datafield->id, $visibleDatafieldIds)) {
                        $widget = $this->getDatafieldWidget($datafield);

                        if ($widget) {
                            $sidebar->addWidget($widget);
                        }
                    }
                }
            }
        }


        //this shall be visible in every case:
        $this->setActionsWidget();


        //actions: always visible, too
        if ($GLOBALS['perm']->have_perm($this->sem_create_perm)) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Neue Veranstaltung anlegen'),
                  URLHelper::getURL('dispatch.php/course/wizard'),
                  Icon::create('add')
            )->asDialog('size=50%');
            $actions->addLink(
                _('Diese Seitenleiste konfigurieren'),
                URLHelper::getURL('dispatch.php/admin/courses/sidebar'),
                Icon::create('admin')
            )->asDialog();


            $sidebar->addWidget($actions, 'links');
        }

        //the view filter's visibility is configurable:
        if (in_array('viewFilter', $visibleElements)) {
            $this->setViewWidget($this->view_filter);
        }

        //"export as Excel" is always visible:
        if ($this->sem_create_perm) {
            $params = [];

            if ($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT) {
                $params['search'] = $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT;
            }
            $export = new ExportWidget();
            $export->addLink(
                _('Als Excel exportieren'),
                 URLHelper::getURL('dispatch.php/admin/courses/export_csv', $params),
                 Icon::create('file-excel')
            )->asDialog('size=auto');
            $sidebar->addWidget($export);
        }

        foreach (PluginEngine::getPlugins(AdminCourseWidgetPlugin::class) as $plugin) {
            foreach ($plugin->getWidgets() as $name => $widget) {
                $position = $widget->getPositionInSidebar();
                if ($position) {
                    $sidebar->insertWidget($widget, $position, $name);
                } else {
                    $sidebar->addWidget($widget, $name);
                }
            }
        }
    }


    /**
     * Common tasks for all actions
     *
     * @param String $action Called action
     * @param Array  $args   Possible arguments
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if ($GLOBALS['perm']->have_perm('admin')) {
            Navigation::activateItem('/browse/my_courses/list');
        } else {
            Navigation::activateItem('/browse/admincourses');
        }

        // we are defintely not in an lecture or institute
        closeObject();

        //delete all temporary permission changes
        if (is_array($_SESSION)) {
            foreach (array_keys($_SESSION) as $key) {
                if (strpos($key, 'seminar_change_view_') === 0) {
                    unset($_SESSION[$key]);
                }
            }
        }

        $this->insts = Institute::getMyInstitutes($GLOBALS['user']->id);

        if (empty($this->insts) && !$GLOBALS['perm']->have_perm('root')) {
            PageLayout::postError(_('Sie wurden noch keiner Einrichtung zugeordnet'));
        }

        // Semester selection
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE) {
            $this->semester = Semester::find($GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE);
        }

        if (Request::get('reset-search')) {
            $GLOBALS['user']->cfg->delete('ADMIN_COURSES_SEARCHTEXT');
        }

        PageLayout::setHelpKeyword('Basis.Veranstaltungen');
        PageLayout::setTitle(_('Verwaltung von Veranstaltungen und Einrichtungen'));
        // Add admission functions.
        PageLayout::addScript('studip-admission.js');
        $this->max_show_courses = 500;
    }

    /**
     * Show all courses with more options
     */
    public function index_action()
    {
        $this->fields = $this->getViewFilters();
        $this->sortby = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT ?? 'name';
        $this->sortflag = $GLOBALS['user']->cfg->MEINE_SEMINARE_SORT_FLAG ?? 'ASC';

        $this->buildSidebar();

        PageLayout::addHeadElement('script', [
            'type' => 'text/javascript',
        ], sprintf(
              'window.AdminCoursesStoreData = %s;',
              json_encode($this->getStoreData())
        ));
    }

    private function getStoreData(): array
    {
        $configuration = User::findCurrent()->getConfiguration();

        $institut_id = $configuration->MY_INSTITUTES_DEFAULT && $configuration->MY_INSTITUTES_DEFAULT !== 'all'
                     ? $configuration->MY_INSTITUTES_DEFAULT
                     : null;

        $filters = array_merge(
            array_merge(...PluginEngine::sendMessage(AdminCourseWidgetPlugin::class, 'getFilters')),
            $this->getDatafieldFilters(),
            [
                'institut_id'    => $institut_id,
                'search'         => $configuration->ADMIN_COURSES_SEARCHTEXT,
                'semester_id'    => $configuration->MY_COURSES_SELECTED_CYCLE,
                'course_type'    => $configuration->MY_COURSES_TYPE_FILTER,
                'stgteil'        => $configuration->MY_COURSES_SELECTED_STGTEIL,
                'teacher_filter' => $configuration->ADMIN_COURSES_TEACHERFILTER,
            ]
        );

        return [
            'setActivatedFields' => $this->getFilterConfig(),
            'setActionArea' => $configuration->MY_COURSES_ACTION_AREA ?? '1',
            'setFilter' => array_filter($filters),
        ];
    }

    private function getDatafieldFilters(): array
    {
        $visibleElements = $this->getActiveElements();
        if (empty($visibleElements['datafields'])) {
            return [];
        }

        $datafields = DataField::getDataFields('sem');
        $config = $GLOBALS['user']->cfg->ADMIN_COURSES_DATAFIELDS_FILTERS;

        $datafields = array_filter($datafields, function (Datafield $datafield) use ($visibleElements, $config) {
            return in_array($datafield->id, $visibleElements['datafields'])
                && isset($config[$datafield->id]);
        });

        $result = [];
        foreach ($datafields as $datafield) {
            $result["df_{$datafield->id}"] = $config[$datafield->id];
        }
        return $result;
    }

    public function search_action()
    {
        $this->processFilters();

        $filter = AdminCourseFilter::get();
        if (Request::option('course_id')) { //we have only one course and want to see if that course is part of the result set
            $filter->query->where('course_id', 'seminare.Seminar_id = :course_id', ['course_id' => Request::option('course_id')]);
        }
        PluginEngine::sendMessage(AdminCourseWidgetPlugin::class, 'applyFilters', $filter);

        $count = $filter->countCourses();
        if ($count > $this->max_show_courses && !Request::submitted('without_limit')) {
            $this->render_json([
                'count' => $count
            ]);
            return;
        }
        $courses = AdminCourseFilter::get()->getCourses();

        $data = [
            'data' => []
        ];
        if (Request::submitted('activated_fields')) {
            $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', json_encode(Request::getArray('activated_fields')));
        }
        $activated_fields = $this->getFilterConfig();

        $GLOBALS['user']->cfg->store('MY_COURSES_ACTION_AREA', Request::option('action'));
        foreach ($courses as $course) {
            if ($course->parent_course && !Request::option('course_id')) {
                continue;
            }
            $data['data'][] = $this->getCourseData($course, $activated_fields);
            foreach ($course->children as $childcourse) {
                $data['data'][] = $this->getCourseData($childcourse, $activated_fields);
            }
        }
        $tf = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'] . '/app/views');
        switch ($GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA) {
            case 1:
            case 2:
            case 3:
            case 4:
                break;
            case 8: //Sperrebenen
                $template = $tf->open('admin/courses/lock_preselect');
                $template->course = $course;
                $template->all_lock_rules = new SimpleCollection(array_merge(
                    [[
                        'name'    => '--' . _('keine Sperrebene') . '--',
                        'lock_id' => 'none'
                    ]],
                    LockRule::findAllByType('sem')
                ));
                $data['buttons_top'] = $template->render();
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(_('Sperrebenen'), 'locking_button', ['formaction' => URLHelper::getURL('dispatch.php/admin/courses/set_lockrule')]);
                break;
            case 9: //Sichtbarkeit
                $data['buttons_top'] = '<label>'._('Alle auswählen').'<input type="checkbox" data-proxyfor=".course-admin td:last-child :checkbox"></label>';
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(_('Sichtbarkeit'), 'visibility_button', ['formaction' => URLHelper::getURL('dispatch.php/admin/courses/set_visibility')]);
                break;
            case 10: //Zusatzangaben
                $template = $tf->open('admin/courses/aux_preselect');
                $template->course = $course;
                $template->aux_lock_rules = AuxLockRule::findBySQL('1 ORDER BY name ASC');
                $data['buttons_top'] = $template->render();
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(_('Zusatzangaben'), 'aux_button', ['formaction' => URLHelper::getURL('dispatch.php/admin/courses/set_aux_lockrule')]);
                break;
            case 11: //Veranstaltung kopieren
                break;
            case 14: //Zugangsberechtigungen
                break;
            case 16: //Löschen
                $data['buttons_top'] = '<label>'._('Alle auswählen').'<input type="checkbox" data-proxyfor=".course-admin td:last-child :checkbox"></label>';
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(_('Löschen'), 'deleting_button', ['formaction' => URLHelper::getURL('dispatch.php/course/archive/confirm')]);
                break;
            case 17: //Gesperrte Veranstaltungen
                $data['buttons_top'] = '<label>'._('Alle auswählen').'<input type="checkbox" data-proxyfor=".course-admin td:last-child :checkbox"></label>';
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(_('Einstellungen speichern'), 'locking_button', ['formaction' => URLHelper::getURL('dispatch.php/admin/courses/set_locked')]);
                break;
            case 18: //Startsemester
                break;
            case 19: //LV-Gruppen
                break;
            case 20: //Notiz
                break;
            case 21: //Mehrfachzuordnung Studienbereiche
                $data['buttons_top'] = '<label>' . _('Alle auswählen') .
                    '<input type="checkbox" data-proxyfor=".course-admin td:last-child :checkbox"></label>';
                $data['buttons_bottom'] = (string) \Studip\Button::createAccept(
                    _('Mehrfachzuordnung von Studienbereichen'), 'batch_assign_semtree',
                    [
                        'formaction' => URLHelper::getURL('dispatch.php/admin/tree/batch_assign_semtree'),
                        'data-dialog' => 'size=big'
                    ]);
                break;
            default:
                foreach (PluginManager::getInstance()->getPlugins('AdminCourseAction') as $plugin) {
                    if ($GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA === get_class($plugin)) {
                        $multimode = $plugin->useMultimode();
                        if ($multimode) {
                            $data['buttons_top'] = '<label>'._('Alle auswählen').'<input type="checkbox" data-proxyfor=".course-admin td:last-child :checkbox"></label>';
                            if ($multimode instanceof Flexi_Template) {
                                $data['buttons_bottom'] = $multimode->render();
                            } elseif ($multimode instanceof \Studip\Button) {
                                $data['buttons_bottom'] = (string) $multimode;
                            } elseif (is_string($multimode)) {
                                $data['buttons_bottom'] = (string) \Studip\Button::create($multimode, '', ['formaction' => $plugin->getAdminActionURL()]);
                            } else {
                                $data['buttons_bottom'] = (string) \Studip\Button::create(_('Speichern'), '', ['formaction' => $plugin->getAdminActionURL()]);
                            }
                        }
                        break;
                    }
                }
        }
        if (!isset($data['buttons_top'])) {
            $data['buttons_top'] = '';
        }
        if (!isset($data['buttons_bottom'])) {
            $data['buttons_bottom'] = '';
        }

         $this->render_json($data);
    }

    private function processFilters(): void
    {
        $filters = Request::getArray('filters');
        $config = User::findCurrent()->getConfiguration();

        // Simple filters
        $mapping = [
            'search'         => 'ADMIN_COURSES_SEARCHTEXT',
            'semester_id'    => 'MY_COURSES_SELECTED_CYCLE',
            'stgteil'        => 'MY_COURSES_SELECTED_STGTEIL',
            'teacher_filter' => 'ADMIN_COURSES_TEACHERFILTER',
            'course_type'    => 'MY_COURSES_TYPE_FILTER',
            'institut_id'    => 'MY_INSTITUTES_DEFAULT',
        ];

        foreach ($mapping as $key => $field) {
            if (isset($filters[$key])) {
                $config->store($field, $filters[$key]);
            }

            unset($filters[$key]);
        }

        // Datafield filters
        $activeSidebarElements = $this->getActiveElements();

        $datafields_filters = $GLOBALS['user']->cfg->ADMIN_COURSES_DATAFIELDS_FILTERS;
        foreach (DataField::getDataFields('sem') as $datafield) {
            $key = "df_{$datafield->id}";

            if (
                !empty($filters[$key])
                && in_array($datafield->id, $activeSidebarElements['datafields'])
            ) {
                $datafields_filters[$datafield->id] = $filters[$key];
            } else {
                unset($datafields_filters[$datafield->id]);
            }
        }
        $config->store('ADMIN_COURSES_DATAFIELDS_FILTERS', $datafields_filters);

        // Plugin filters
        foreach (PluginEngine::getPlugins(AdminCourseWidgetPlugin::class) as $plugin) {
            $plugin_filters = array_intersect_key(
                $filters,
                $plugin->getFilters()
            );
            $plugin->setFilters($plugin_filters);
        }
    }

    protected function getCourseData(Course $course, $activated_fields)
    {
        $d = [
            'id' => $course->id,
            'parent_course' => $course->parent_course
        ];
        if (in_array('name', $activated_fields)) {
            $params = tooltip2(_('Veranstaltungsdetails anzeigen'));
            $params['style'] = 'cursor: pointer';
            $d['name'] = '<a href="'.URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]).'">'
                . htmlReady($course->name)
                .'</a> '
                .'<a href="'.URLHelper::getLink('dispatch.php/course/details/index/'. $course->id).'" data-dialog><button class="undecorated">'.Icon::create('info-circle', Icon::ROLE_INACTIVE)->asImg($params).'</button></a> '
                .(!$course->visible ? _('(versteckt)') : '');
        }
        if (in_array('number', $activated_fields)) {
            $d['number'] = '<a href="'.URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]).'">'
                .$course->veranstaltungsnummer
                .'</a>';
        }
        if (in_array('avatar', $activated_fields)) {
            $d['avatar'] = '<a href="'.URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id]).'">'
                .CourseAvatar::getAvatar($course->getId())->getImageTag(Avatar::SMALL, ['title' => $course->name])
                ."</a>";
        }
        if (in_array('type', $activated_fields)) {
            $semtype = $course->getSemType();
            $d['type'] = $semtype['name'];
        }
        if (in_array('room_time', $activated_fields)) {
            $seminar = new Seminar($course);
            $d['room_time'] = $seminar->getDatesHTML([
                'show_room'   => true,
            ]) ?: _('nicht angegeben');
        }
        if (in_array('semester', $activated_fields)) {
            $d['semester'] = $course->semester_text;
        }
        if (in_array('institute', $activated_fields)) {
            $d['institute'] = $course->home_institut ? $course->home_institut->name : $course->institute;
        }
        if (in_array('requests', $activated_fields)) {
            $d['requests'] = '<a href="'.URLHelper::getLink('dispatch.php/course/room_requests', ['cid' => $course->id]).'">'.count($course->room_requests)."</a>";
        }
        if (in_array('teachers', $activated_fields)) {
            $teachers = $this->getTeacher($course->id);
            $teachers = array_map(function ($teacher) {
                return '<a href="'.URLHelper::getLink('dispatch.php/profile', ['username' => $teacher['username']]) .'">'. htmlReady($teacher['fullname']).'</a>';
            }, $teachers);
            $d['teachers'] = implode(', ', $teachers);
        }
        if (in_array('members', $activated_fields)) {
            $d['members'] = '<a href="'.URLHelper::getLink('dispatch.php/course/members', ['cid' => $course->id]).'">'
                .$course->getNumParticipants()
                .'</a>';
        }
        if (in_array('waiting', $activated_fields)) {
            $d['waiting'] = '<a href="'.URLHelper::getLink('dispatch.php/course/members', ['cid' => $course->id]).'">'
                .$course->getNumWaiting()
                .'</a>';
        }
        if (in_array('preliminary', $activated_fields)) {
            $d['preliminary'] = '<a href="'.URLHelper::getLink('dispatch.php/course/members', ['cid' => $course->id]).'">'
                .$course->getNumPrelimParticipants()
                .'</a>';
        }
        if (in_array('contents', $activated_fields)) {
            $icons = [];
            foreach ($course->tools as $tool) {
                $module = $tool->getStudipModule();
                if ($module) {
                    $last_visit = object_get_visit($course->id, $module->getPluginId());
                    $nav = $module->getIconNavigation($course->id, $last_visit, $GLOBALS['user']->id);
                    if (isset($nav) && $nav->isVisible(true)) {
                        $icons[] = $nav;
                    }
                }
            }
            $d['contents'] = '<div class="icons">
                <ul class="my-courses-navigation">';

            foreach ($icons as $icon) {
                $d['contents'] .= '<li class="my-courses-navigation-item '. ($icon->getImage()->signalsAttention() ? 'my-courses-navigation-important' : '').'">
                        <a href="'. URLHelper::getLink('seminar_main.php', ['auswahl' => $course->id, 'redirect_to' => $icon->getURL()]).'"'. ($icon->getTitle() ? ' title="'.htmlReady($icon->getTitle()).'"' : '') .'>
                            '. $icon->getImage()->asImg(20) .'
                        </a>
                    </li>';
            }
            $d['contents'] .= '</ul></div>';
        }
        if (in_array('last_activity', $activated_fields)) {
            $d['last_activity'] = strftime('%x', lastActivity($course->id));
        }

        foreach (PluginManager::getInstance()->getPlugins('AdminCourseContents') as $plugin) {
            foreach ($plugin->adminAvailableContents() as $index => $label) {
                if (in_array($plugin->getPluginId() . '_' . $index, $activated_fields)) {
                    $content = $plugin->adminAreaGetCourseContent($course, $index);
                    $d[$plugin->getPluginId()."_".$index] = $content instanceof Flexi_Template ? $content->render() : $content;
                }
            }
        }
        $tf = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH'].'/app/views');

        switch ($GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA) {
            case 1:
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Grunddaten'),
                    URLHelper::getURL('dispatch.php/course/basicdata/view', ['cid' => $course->id]),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 2:
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Studienbereiche'),
                    URLHelper::getURL('dispatch.php/course/study_areas/show', ['cid' => $course->id, 'from' => 'admin/courses']),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 3:
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Zeiten/Räume'),
                    URLHelper::getURL('dispatch.php/course/timesrooms/index', ['cid' => $course->id, 'cmd' => 'applyFilter']),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 4:
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Raumanfragen'),
                    URLHelper::getURL('dispatch.php/course/room_requests/index', ['cid' => $course->id, 'origin' => 'admin_courses']),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 8: //Sperrebenen
                $template = $tf->open('admin/courses/lock');
                $template->course = $course;
                $template->aux_lock_rules = AuxLockRule::findBySQL('1 ORDER BY name ASC');
                $template->all_lock_rules = new SimpleCollection(array_merge(
                    [[
                        'name'    => '--' . _('keine Sperrebene') . '--',
                        'lock_id' => 'none'
                    ]],
                    LockRule::findAllByType('sem')
                ));
                $d['action'] = $template->render();
                break;
            case 9: //Sichtbarkeit
                $d['action'] = '<input type="hidden" name="all_sem[]" value="'.htmlReady($course->id).'"><input type="checkbox" name="visibility['.$course->id.']" '.($course->visible ? ' checked ' : '').'value="1">';
                break;
            case 10: //Zusatzangaben
                $template = $tf->open('admin/courses/aux-select');
                $template->course = $course;
                $template->aux_lock_rules = AuxLockRule::findBySQL('1 ORDER BY name ASC');
                $d['action'] = $template->render();
                break;
            case 11: //Veranstaltung kopieren
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Kopieren'),
                    URLHelper::getURL('dispatch.php/course/wizard/copy/' . $course->id),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 14: //Zugangsberechtigungen
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Zugangsberechtigungen'),
                    URLHelper::getURL('dispatch.php/course/admission', ['cid' => $course->id]),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 16: //Löschen
                $d['action'] = '<input type="checkbox" name="archiv_sem[]" value="'.htmlReady($course->id).'" aria-label="'.htmlReady(sprintf(_('Veranstaltung %s löschen'), $course->getFullName())).'">';
                break;
            case 17: //Gesperrte Veranstaltungen
                $cs = CourseSet::getSetForCourse($course->id);
                if ($cs) {
                    $locked = true;
                    $disabled = !$cs->hasAdmissionRule('LockedAdmission');
                } else {
                    $locked = false;
                    $disabled = false;
                }
                $d['action'] = '<input type="hidden" name="all_sem[]" value="'.htmlReady($course->id).'"><input type="checkbox" name="admission_locked['.$course->getId().']" '.($locked ? 'checked' : '').' '.($disabled ? 'disabled' : '').' value="1" aria-label="'.htmlReady(sprintf(_('Veranstaltung %s sperren'), $course->getFullName())).'">';
                break;
            case 18: //Startsemester
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('Startsemester'),
                    URLHelper::getURL('dispatch.php/course/timesrooms/editSemester', ['cid' => $course->id, 'origin' => 'admin_courses']),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 19: //LV-Gruppen
                $d['action'] = (string) \Studip\LinkButton::create(
                    _('LV-Gruppen'),
                    URLHelper::getURL('dispatch.php/course/lvgselector', ['cid' => $course->id, 'from' => 'admin/courses']),
                    ['data-dialog' => '', 'role' => 'button']
                );
                break;
            case 20: //Notiz
                $method = $course->config->COURSE_ADMIN_NOTICE ? 'createHasNotice' : 'createHasNoNotice';
                $d['action'] = (string) \Studip\LinkButton::$method(
                    _('Notiz'),
                    URLHelper::getURL('dispatch.php/admin/courses/notice/'.$course->id),
                    [
                        'data-dialog' => 'size=auto',
                        'title' => $course->config->COURSE_ADMIN_NOTICE,
                        'role' => 'button'
                    ]
                );
                break;
            case 21: //Mehrfachzuweisung Studienbereiche
                $template = $tf->open('admin/courses/batch_assign_semtree');
                $template->course = $course;
                $d['action'] = $template->render();
                break;
            default:
                foreach (PluginManager::getInstance()->getPlugins('AdminCourseAction') as $plugin) {
                    if ($GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA === get_class($plugin)) {
                        $output = $plugin->getAdminCourseActionTemplate($course->getId());
                        $d['action'] = $output instanceof Flexi_Template ? $output->render() : (string) $output;
                        break;
                    }
                }
        }
        $d['completion'] = $course->completion;
        return $d;
    }

    /**
     * This action just stores the new settings for sorting the table of courses.
     * @return void
     */
    public function sort_action()
    {
        if (Request::isPost()) {
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT', Request::get('sortby'));
            $GLOBALS['user']->cfg->store('MEINE_SEMINARE_SORT_FLAG', Request::get('sortflag'));
        }
        $this->render_nothing();
    }


    /**
     * The sidebar action is responsible for showing a dialog
     * that lets the user configure what elements of the sidebar are visible
     * and which will be invisible.
     *
     * @return null This method does not return any value.
     */
    public function sidebar_action()
    {
        if (Request::get('updateConfig', false)) {
            /*
                The user has changed the configuration.
                Collect the activated elements:
            */

            $searchActive = Request::get('searchActive');
            $instituteActive = Request::get('instituteActive');
            $semesterActive = Request::get('semesterActive');
            $stgteilActive = Request::get('stgteilActive');
            $courseTypeActive = Request::get('courseTypeActive');
            $teacherActive = Request::get('teacherActive');
            $viewFilterActive = Request::get('viewFilterActive');
            $activeDatafields = Request::getArray('activeDatafields');

            /*
                Update or create an entry for the current user
                in the user configuration table.
            */
            $activeArray = [];
            if ($searchActive) {
                $activeArray['search'] = true;
            }
            if ($instituteActive) {
                $activeArray['institute'] = true;
            }
            if ($semesterActive) {
                $activeArray['semester'] = true;
            }
            if ($stgteilActive) {
                $activeArray['stgteil'] = true;
            }
            if ($courseTypeActive) {
                $activeArray['courseType'] = true;
            }
            if ($teacherActive) {
                $activeArray['teacher'] = true;
            }
            if ($viewFilterActive) {
                $activeArray['viewFilter'] = true;
            }

            if ($activeDatafields) {
                $activeArray['datafields'] = $activeDatafields;
            }

            //store the configuration value:
            $this->setActiveElements($activeArray);

            $this->redirect('admin/courses/index');
        } else {
            /*
                The user accesses the page to check the current configuration.
            */

            $this->datafields = DataField::getDataFields('sem');

            $this->userSelectedElements = $this->getActiveElements();

            //add the last activity for each Course object:
            $this->lastActivities = [];
        }
    }

    public function get_stdgangteil_selector_action($institut_id)
    {
        $selector = $this->getStgteilSelector($institut_id);
        $this->render_text($selector->render(['base_class' => 'sidebar']));
    }

    public function get_teacher_selector_action($institut_id)
    {
        $selector = $this->getTeacherWidget($institut_id);
        $this->render_text($selector->render(['base_class' => 'sidebar']));
    }


    /**
     * Export action
     */
    public function export_csv_action()
    {
        $filter_config = Request::getArray('fields');

        if (count($filter_config) > 0) {
            $courses = AdminCourseFilter::get()->getCourses();

            $view_filters = $this->getViewFilters();

            $data = [];

            foreach ($courses as $course) {
                $sem = new Seminar($course);
                $row = [];

                if (in_array('number', $filter_config)) {
                    $row['number'] = $course['VeranstaltungsNummer'];
                }

                if (in_array('name', $filter_config)) {
                    $row['name'] = $course->name;
                }

                if (in_array('type', $filter_config)) {
                    $row['type'] = sprintf(
                        '%s: %s',
                        $course->getSemClass()['name'],
                        $course->getSemType()['name']
                    );
                }

                if (in_array('room_time', $filter_config)) {
                    $_room = $sem->getDatesExport([
                        'semester_id' => $this->semester->id,
                        'show_room' => true
                    ]);
                    $row['room_time'] = $_room ?: _('nicht angegeben');
                }

                if (in_array('requests', $filter_config)) {
                    $row['requests'] = $course->room_requests->count();
                }

                if (in_array('teachers', $filter_config)) {
                    $row['teachers'] = implode(
                        ', ',
                        array_map(
                            function ($d) {
                                return $d->getUserFullName();
                            },
                            CourseMember::findByCourseAndStatus($course->id, 'dozent')
                        )
                    );
                }

                if (in_array('members', $filter_config)) {
                    $row['members'] = $course->getNumParticipants();
                }

                if (in_array('waiting', $filter_config)) {
                    $row['waiting'] = $course->getNumWaiting();
                }

                if (in_array('preliminary', $filter_config)) {
                    $row['preliminary'] = $course->getNumPrelimParticipants();
                }

                if (in_array('last_activity', $filter_config)) {
                    $row['last_activity'] = strftime('%x', lastActivity($course->id));
                }

                if (in_array('semester', $filter_config)) {
                    $row['semester'] = $course->getTextualSemester();
                }

                if (in_array('institute', $filter_config)) {
                    $row['institute'] = $course->home_institut ? (string) $course->home_institut['name'] : $course['institut_id'];
                }

                foreach (PluginManager::getInstance()->getPlugins('AdminCourseContents') as $plugin) {
                    foreach ($plugin->adminAvailableContents() as $index => $label) {
                        if (in_array($plugin->getPluginId() . "_" . $index, $filter_config)) {
                            $content = $plugin->adminAreaGetCourseContent($course, $index);
                            $row[$plugin->getPluginId() . "_" . $index] = strip_tags(is_a($content, 'Flexi_Template')
                                ? $content->render()
                                : $content
                            );
                        }
                    }
                }

                $data[$course->id] = $row;
            }

            $captions = [];
            foreach ($filter_config as $index) {
                $captions[$index] = $view_filters[$index];
            }
            foreach (PluginManager::getInstance()->getPlugins('AdminCourseContents') as $plugin) {
                foreach ($plugin->adminAvailableContents() as $index => $label) {
                    if (in_array($plugin->getPluginId() . "_" . $index, $filter_config)) {
                        $captions[$plugin->getPluginId() . "_" . $index] = $label;
                    }
                }
            }

            $tmpname = md5(uniqid('Veranstaltungsexport'));
            if (array_to_csv($data, $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
                $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                    $tmpname,
                    'Veranstaltungen_Export.csv'
                ));
                return;
            }
        } else {
            PageLayout::setTitle(_('Spalten zum Export auswählen'));
            $this->fields = $this->getViewFilters();
            $this->selection = $this->getFilterConfig();
        }
    }


    /**
     * Set the lockrules of courses
     */
    public function set_lockrule_action()
    {
        if (!$GLOBALS['perm']->have_perm('admin')) {
            throw new AccessDeniedException();
        }
        $result = false;
        $courses = Request::getArray('lock_sem');
        $errors = [];

        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
                    // force to pre selection
                    if (Request::get('lock_sem_all') && Request::submitted('all')) {
                        $value = Request::get('lock_sem_all');
                    }

                    $course = Course::find($course_id);
                    if ($value === 'none') {
                        $value = null;
                    }

                    if ($course->lock_rule === $value) {
                        continue;
                    }

                    $course->setValue('lock_rule', $value);
                    if (!$course->store()) {
                        $errors[] = $course->name;
                    } else {
                        $result = true;
                    }
                }
            }

            if ($result) {
                PageLayout::postSuccess(_('Die gewünschten Änderungen wurden erfolgreich durchgeführt!'));
            }
            if ($errors) {
                PageLayout::postError(
                    _('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'),
                    array_map('htmlReady', $errors)
                );
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Lock or unlock courses
     */
    public function set_locked_action()
    {
        $admission_locked = Request::getArray('admission_locked');

        $all_courses = Request::getArray('all_sem');

        $course_set_id = CourseSet::getGlobalLockedAdmissionSetId();
        $log_msg = '';
        foreach ($all_courses as $course_id) {
            if ($GLOBALS['perm']->have_studip_perm('dozent', $course_id)) {
                $set = CourseSet::getSetForCourse($course_id);

                if (!is_null($set)) {
                    if (!$set->hasAdmissionRule('LockedAdmission')) {
                        continue;
                    }

                    if ($set->hasAdmissionRule('LockedAdmission') && !isset($admission_locked[$course_id])) {
                        if (CourseSet::removeCourseFromSet($set->getId(), $course_id)) {
                            $log_msg = _('Veranstaltung wurde entsperrt');
                        }
                    }
                }

                if (is_null($set) && isset($admission_locked[$course_id])) {
                    if (CourseSet::addCourseToSet($course_set_id, $course_id)) {
                        $log_msg = sprintf(_('Veranstaltung wurde gesperrt, set_id: %s'), $course_set_id);
                    }
                }

                if ($log_msg) {
                    StudipLog::log('SEM_CHANGED_ACCESS', $course_id, null, $log_msg);
                }
            }
        }

        PageLayout::postSuccess(_('Die gewünschten Änderungen wurden ausgeführt!'));
        $this->redirect('admin/courses/index');
    }


    /**
     * Set the visibility of a course
     */
    public function set_visibility_action()
    {
        $result = false;
        $visibilites = Request::intArray('visibility');
        $all_courses = Request::getArray('all_sem');
        $errors = [];

        if (!empty($all_courses)) {
            foreach ($all_courses as $course_id) {
                if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                    $course = Course::find($course_id);

                    if ($course->isOpenEnded() || $course->end_semester->visible) {
                        $visibility = $visibilites[$course_id] ?: 0;

                        if ($course->visible == $visibility) {
                            continue;
                        }

                        $course->visible = $visibility;
                        if (!$course->store()) {
                            $errors[] = $course->name;
                        } else {
                            $result = true;
                            StudipLog::log($visibility ? 'SEM_VISIBLE' : 'SEM_INVISIBLE', $course->id);
                        }
                    }
                }
            }

            if ($result) {
                PageLayout::postSuccess(_('Die Sichtbarkeit wurde bei den gewünschten Veranstatungen erfolgreich geändert!'));
            }
            if ($errors) {
                PageLayout::postError(
                    _('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'),
                    array_map('htmlReady', $errors)
                );
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Set the additional course informations
     */
    public function set_aux_lockrule_action()
    {
        $result = false;
        $courses = Request::getArray('lock_sem');
        $lock_sem_forced = Request::getArray('lock_sem_forced');
        $errors = [];
        if (!empty($courses)) {
            foreach ($courses as $course_id => $value) {
                if ($GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
                    // force to pre selection
                    if (Request::submitted('all')) {
                        $value = Request::get('lock_sem_all');
                        $value_forced = Request::int('aux_all_forced', 0);
                    } else {
                        $value_forced = $lock_sem_forced[$course_id] ?? 0;
                    }

                    $course = Course::find($course_id);

                    if (!$value) {
                        $value_forced = 0;
                    }

                    $course->setValue('aux_lock_rule', $value);
                    $course->setValue('aux_lock_rule_forced', $value_forced);

                    $ok = $course->store();
                    if ($ok === false) {
                        $errors[] = $course->name;
                    } elseif ($ok) {
                        $result = true;
                    }
                }
            }

            if ($result) {
                PageLayout::postSuccess(_('Die gewünschten Änderungen wurden erfolgreich durchgeführt!'));
            }
            if ($errors) {
                PageLayout::postError(
                    _('Bei den folgenden Veranstaltungen ist ein Fehler aufgetreten'),
                    array_map('htmlReady', $errors)
                );
            }
        }
        $this->redirect('admin/courses/index');
    }


    /**
     * Marks a course as complete/incomplete.
     *
     * @param String $course_id Id of the course
     */
    public function toggle_complete_action($course_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $course_id)) {
            throw new AccessDeniedException();
        }
        $course = Course::find($course_id);
        $course->completion = ((int)$course->completion + 1) % 3;
        $course->store();

        if (Request::isXhr()) {
            $this->render_json([
                'state' => (int)$course->completion,
                'label' => $course->getCompetionLabel(),
            ]);
        } else {
            $this->redirect('admin/courses/index#course-' . $course_id);
        }
    }

    /**
     * Changes the notice for a course.
     *
     * @param  string $course_id
     */
    public function notice_action(Course $course)
    {
        if (Request::isPost()) {
            $course->config->store('COURSE_ADMIN_NOTICE', trim(Request::get('notice')));

            if (Request::isXhr()) {
                $this->response->add_header('X-Dialog-Execute', 'STUDIP.AdminCourses.App.loadCourse');
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_text($course->id);
            } else {
                $this->redirect($this->indexURL("#course-{$course->id}"));
            }
            return;
        }

        $this->course = $course;
        $this->notice = $course->config->COURSE_ADMIN_NOTICE;
    }


    /**
     * Return a specifically action or all available actions
     * @param null $selected
     * @return array
     */
    private function getActions($selected = null)
    {
        // array for the avaiable modules
        $sem_filter = $this->semester ? $this->semester->beginn : 'all';
        $actions = [
            1 => [
                'name'       => _('Grunddaten'),
                'title'      => _('Grunddaten'),
                'url'        => 'dispatch.php/course/basicdata/view?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ],
            2 => [
                'name'       => _('Studienbereiche'),
                'title'      => _('Studienbereiche'),
                'url'        => 'dispatch.php/course/study_areas/show/?cid=%s&from=admin/courses',
                'attributes' => ['data-dialog' => 'size=big'],
            ],
            3 => [
                'name'       => _('Zeiten/Räume'),
                'title'      => _('Zeiten/Räume'),
                'url'        => 'dispatch.php/course/timesrooms/index?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
                'params'     => [
                    'newFilter' => $sem_filter,
                    'cmd'       => 'applyFilter'
                ],
            ],
            8 => [
                'name'      => _('Sperrebene'),
                'title'     => _('Sperrebenen'),
                'url'       => 'dispatch.php/admin/courses/set_lockrule',
                'multimode' => true,
                'partial'   => 'lock.php',
            ],
            9 => [
                'name'      => _('Sichtbarkeit'),
                'title'     => _('Sichtbarkeit'),
                'url'       => 'dispatch.php/admin/courses/set_visibility',
                'multimode' => true,
                'partial'   => 'visibility.php',
            ],
            10 => [
                'name'      => _('Zusatzangaben'),
                'title'     => _('Zusatzangaben'),
                'url'       => 'dispatch.php/admin/courses/set_aux_lockrule',
                'multimode' => true,
                'partial'   => 'aux-select.php',
            ],
            11 => [
                'name'       => _('Veranstaltung kopieren'),
                'title'      => _('Kopieren'),
                'url'        => 'dispatch.php/course/wizard/copy/%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ],
            14 => [
                'name'       => 'Zugangsberechtigungen',
                'title'      => _('Zugangsberechtigungen'),
                'url'        => 'dispatch.php/course/admission?cid=%s',
                'attributes' => ['data-dialog' => 'size=big'],
            ],
            16 => [
                'name'      => _('Löschen'),
                'title'     => _('Löschen'),
                'url'       => 'dispatch.php/course/archive/confirm',
                'multimode' => true,
                'partial'   => 'add_to_archive.php',
            ],
            17 => [
                'name'      => _('Gesperrte Veranstaltungen'),
                'title'     => _('Einstellungen speichern'),
                'url'       => 'dispatch.php/admin/courses/set_locked',
                'multimode' => true,
                'partial'   => 'admission_locked.php',
            ],
            18 => [
                'name'       => _('Startsemester'),
                'title'      => _('Startsemester'),
                'url'        => 'dispatch.php/course/timesrooms/editSemester?cid=%s&origin=admin_courses',
                'attributes' => ['data-dialog' => 'size=400'],
            ],
            19 => [
                'name'       => _('LV-Gruppen'),
                'title'      => _('LV-Gruppen'),
                'url'        => 'dispatch.php/course/lvgselector?cid=%s&from=admin/courses',
                'attributes' => ['data-dialog' => 'size=big'],
            ],
            20 => [
                'name'       => _('Notiz'),
                'title'      => _('Notiz'),
                'url'        => $this->noticeURL('%s'),
                'attributes' => ['data-dialog' => 'size=auto'],
                'partial'    => 'notice-action.php',
            ],
            21 => [
                'name'       => _('Mehrfachzuordnung von Studienbereichen'),
                'title'      => _('Mehrfachzuordnung von Studienbereichen'),
                'url'        => 'dispatch.php/admin/tree/batch_assign_semtree',
                'dialogform' => true,
                'multimode'  => true,
                'partial'    => 'batch_assign_semtree.php'
            ],
        ];

        if (!$GLOBALS['perm']->have_perm('admin')) {
            unset($actions[8]);
            if (!Config::get()->ALLOW_DOZENT_DELETE) {
                unset($actions[16]);
            }
        }
        if (!$GLOBALS['perm']->have_perm('dozent')) {
            unset($actions[11]);
            unset($actions[16]);
        }

        ksort($actions);

        foreach (PluginManager::getInstance()->getPlugins('AdminCourseAction') as $plugin) {
            $actions[get_class($plugin)] = [
                'name'      => $plugin->getPluginName(),
                'title'     => $plugin->getPluginName(),
                'url'       => $plugin->getAdminActionURL(),
                'attributes' => ['data-dialog' => 'size=big'],
                'multimode' => $plugin->useMultimode()
            ];
        }

        if (is_null($selected)) {
            return $actions;
        }

        return $actions[$selected];
    }


    /**
     * Set and return all needed view filters
     * @return array
     */
    private function getViewFilters()
    {
        $views = [
            'avatar'        => _('Avatar'),
            'number'        => _('Nr.'),
            'name'          => _('Name'),
            'type'          => _('Veranstaltungstyp'),
            'room_time'     => _('Raum/Zeit'),
            'semester'      => _('Semester'),
            'institute'     => _('Einrichtung'),
            'requests'      => _('Raumanfragen'),
            'teachers'      => _('Lehrende'),
            'members'       => _('Teilnehmende'),
            'waiting'       => _('Personen auf Warteliste'),
            'preliminary'   => _('Vorläufige Anmeldungen'),
            'contents'      => _('Inhalt'),
            'last_activity' => _('Letzte Aktivität'),
        ];
        foreach (PluginManager::getInstance()->getPlugins('AdminCourseContents') as $plugin) {
            foreach ($plugin->adminAvailableContents() as $index => $label) {
                $views[$plugin->getPluginId() . "_" . $index] = $label;
            }
        }
        return $views;
    }

    /**
     * Returns all courses matching set criteria.
     *
     * @param array $params Additional parameters
     * @param bool $display_all : boolean should we show all courses or check for a limit of 500 courses?
     * @return array of courses
     */
    private function getCourses($params = [], $display_all = false)
    {
        // Init
        if ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === "all") {
            $inst = new SimpleCollection($this->insts);
            $inst->filter(function ($a) use (&$inst_ids) {
                $inst_ids[] = $a->Institut_id;
            });
        } else {
            //We must check, if the institute ID belongs to a faculty
            //and has the string _i appended to it.
            //In that case we must display the courses of the faculty
            //and all its institutes.
            //Otherwise we just display the courses of the faculty.

            $inst_id = $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;

            $institut = new Institute($inst_id);

            if (!$institut->isFaculty() || $GLOBALS['user']->cfg->MY_INSTITUTES_INCLUDE_CHILDREN) {
                // If the institute is not a faculty or the child insts are included,
                // pick the institute IDs of the faculty/institute and of all sub-institutes.
                $inst_ids[] = $inst_id;
                if ($institut->isFaculty()) {
                    foreach ($institut->sub_institutes->pluck('Institut_id') as $institut_id) {
                        $inst_ids[] = $institut_id;
                    }
                }
            } else {
                // If the institute is a faculty and the child insts are not included,
                // pick only the institute id of the faculty:
                $inst_ids[] = $inst_id;
            }
        }

        $active_elements = $this->getActiveElements();

        $filter = AdminCourseFilter::get(true);

        if ($params['datafields']) {
            foreach ($params['datafields'] as $field_id => $value) {
                $datafield = DataField::find($field_id);
                if ($datafield) {
                    //enable filtering by datafield values:
                    //and use the where-clause for each datafield:
                    $filter->settings['query']['joins']['de_'.$field_id] = [
                        'table' => "datafields_entries",
                        'join' => "LEFT JOIN",
                        'on' => "seminare.seminar_id = de_".$field_id.".range_id"
                    ];
                    $filter->where("(de_".$field_id.".datafield_id = :fieldId_".$field_id." "
                        . "AND de_".$field_id.".content = :fieldValue_".$field_id.") "
                        . ($datafield['default_value'] == $value ? " OR (de_".$field_id.".content IS NULL)" : "")." ",
                        [
                            'fieldId_'.$field_id => $field_id,
                            'fieldValue_'.$field_id => $value
                        ]
                    );
                }
            }
        }

        $filter->where("sem_classes.studygroup_mode = '0'");

        // Get only children of given course
        if (!empty($params['parent_course'])) {
            $filter->where("parent_course = :parent",
                [
                    'parent' => $params['parent_course']
                ]
            );
        }

        if ($active_elements['semester'] && is_object($this->semester)) {
            $filter->filterBySemester($this->semester->getId());
        }
        if ($active_elements['courseType'] && $params['typeFilter'] && $params['typeFilter'] !== "all") {
            $parts = explode('_', $params['typeFilter']);
            $class_filter = $parts[0];
            $type_filter = $parts[1] ?? null;
            if (!$type_filter && !empty($GLOBALS['SEM_CLASS'][$class_filter])) {
                $type_filter = array_keys($GLOBALS['SEM_CLASS'][$class_filter]->getSemTypes());
            }
            $filter->filterByType($type_filter);
        }
        if ($active_elements['search'] && $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT) {
            $filter->filterBySearchString($GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT);
        }
        if ($active_elements['teacher'] && $GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER && ($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER !== "all")) {
            $filter->filterByDozent($GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER);
        }
        if ($active_elements['institute']) {
            $filter->filterByInstitute($inst_ids);
        }
        if ($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL && $GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL !== 'all') {
            $filter->filterByStgTeil($GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL);
        }
        if ($params['sortby'] === "status") {
            $filter->orderBy(sprintf('sem_classes.name %s, sem_types.name %s, VeranstaltungsNummer %s', $params['sortFlag'], $params['sortFlag'], $params['sortFlag']), $params['sortFlag']);
        } elseif ($params['sortby'] === 'institute') {
            $filter->orderBy('Institute.Name', $params['sortFlag']);
        } elseif ($params['sortby']) {
            $filter->orderBy($params['sortby'], $params['sortFlag']);
        }
        $filter->storeSettings();
        $this->count_courses = $filter->countCourses();
        if ($this->count_courses && ($this->count_courses <= $filter->max_show_courses || $display_all)) {
            $courses = $filter->getCourses();
        } else {
            return [];
        }

        $seminars = [];
        if (!empty($courses)) {
            foreach ($courses as $seminar_id => $seminar) {
                $seminars[$seminar_id] = $seminar[0];
                $seminars[$seminar_id]['seminar_id'] = $seminar_id;
                $seminars[$seminar_id]['obj_type'] = 'sem';
                $dozenten = $this->getTeacher($seminar_id);
                $seminars[$seminar_id]['dozenten'] = $dozenten;

                if (in_array('contents', $params['view_filter'])) {
                    $tools = new SimpleCollection(ToolActivation::findbyRange_id($seminar_id, "ORDER BY position"));
                    $visit_data = get_objects_visits([$seminar_id], 0, null, null, $tools->pluck('plugin_id'));
                    $seminars[$seminar_id]['visitdate'] = $visit_data[$seminar_id][0]['visitdate'];
                    $seminars[$seminar_id]['last_visitdate'] = $visit_data[$seminar_id][0]['last_visitdate'];
                    $seminars[$seminar_id]['tools'] = $tools;
                    $seminars[$seminar_id]['navigation'] = MyRealmModel::getAdditionalNavigations(
                        $seminar_id,
                        $seminars[$seminar_id],
                        $seminars[$seminar_id]['sem_class'] ?? null,
                        $GLOBALS['user']->id,
                        $visit_data[$seminar_id]
                    );
                }
                //add last activity column:
                if (in_array('last_activity', $params['view_filter'])) {
                    $seminars[$seminar_id]['last_activity'] = lastActivity($seminar_id);
                }
                if ((int)$this->selected_action === 17) {
                    $seminars[$seminar_id]['admission_locked'] = false;
                    if ($seminar[0]['course_set']) {
                        $set = new CourseSet($seminar[0]['course_set']);
                        if (!is_null($set) && $set->hasAdmissionRule('LockedAdmission')) {
                            $seminars[$seminar_id]['admission_locked'] = 'locked';
                        } else {
                            $seminars[$seminar_id]['admission_locked'] = 'disable';
                        }
                        unset($set);
                    }
                }
            }
        }

        return $seminars;
    }

    /**
     * Returns the teacher for a given cours
     *
     * @param String $course_id Id of the course
     * @return array of user infos [user_id, username, Nachname, fullname]
     */
    private function getTeacher($course_id)
    {
        $teachers   = CourseMember::findByCourseAndStatus($course_id, 'dozent');
        $collection = SimpleCollection::createFromArray($teachers);
        return $collection->map(function (CourseMember $teacher) {
            return [
                'user_id'  => $teacher->user_id,
                'username' => $teacher->username,
                'Nachname' => $teacher->nachname,
                'fullname' => $teacher->getUserFullname('no_title_rev'),
            ];
        });
    }


    /**
     * Adds view filter to the sidebar
     * @param array $configs
     */
    private function setViewWidget()
    {
        $configs         = $this->getFilterConfig();
        $checkbox_widget = new OptionsWidget();
        $checkbox_widget->setTitle(_('Darstellungsfilter'));

        foreach ($this->getViewFilters() as $index => $label) {
            $state = in_array($index, $configs);
            $checkbox_widget->addCheckbox(
                $label,
                $state,
                $this->url_for('admin/courses/set_view_filter/' . $index . '/' . $state),
                null,
                ['onclick' => "$(this).toggleClass(['options-checked', 'options-unchecked']); $(this).attr('aria-checked', $(this).hasClass('options-checked') ? 'true' : 'false'); STUDIP.AdminCourses.App.toggleActiveField('".$index."'); return false;"]
            );
        }
        Sidebar::get()->addWidget($checkbox_widget, 'views');
    }

    /**
     * Adds the institutes selector to the sidebar
     */
    private function setInstSelector()
    {
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(
            _('Einrichtung'),
            $this->url_for('admin/courses/set_selection'),
            'institute'
        );
        $list->class = 'institute-list';

        if ($GLOBALS['perm']->have_perm('root') || (count($this->insts) > 1)) {
            $list->addElement(new SelectElement(
                '',
                $GLOBALS['perm']->have_perm('root') ? _('Alle') : _('Alle meine Einrichtungen'),
                !$GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT),
                'select-all'
            );
        }

        foreach ($this->insts as $institut) {
            $list->addElement(
                new SelectElement(
                    $institut['Institut_id'],
                    (!$institut['is_fak'] ? ' ' : '') . $institut['Name'],
                    $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id']
                ),
                'select-' . $institut['Institut_id']
            );

            //check if the institute is a faculty.
            //If true, then add another option to display all courses
            //from that faculty and all its institutes.

            //$institut is an array, we can't use the method isFaculty() here!
            if ($institut['fakultaets_id'] === $institut['Institut_id']) {
                $list->addElement(
                    new SelectElement(
                        $institut['Institut_id'] . '_withinst', //_withinst = with institutes
                        ' ' . $institut['Name'] . ' +' . _('Institute'),
                        ($GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT === $institut['Institut_id'] && $GLOBALS['user']->cfg->MY_INSTITUTES_INCLUDE_CHILDREN)
                    ),
                    'select-' . $institut['Name'] . '-with_institutes'
                );
            }
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.changeFiltersDependendOnInstitute($(this).find('select').val()); return false;");

        $sidebar->addWidget($list, 'filter_institute');
    }

    /**
     * Adds the semester selector to the sidebar
     */
    private function setSemesterSelector()
    {
        $semesters = array_reverse(Semester::getAll());
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Semester'), $this->url_for('admin/courses/set_selection'), 'sem_select');
        $list->addElement(new SelectElement('', _('Alle')), 'sem_select-all');
        foreach ($semesters as $semester) {
            $list->addElement(new SelectElement(
                $semester->id,
                $semester->name,
                $semester->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE
            ), 'sem_select-' . $semester->id);
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({semester_id: $(this).find('select').val()}); return false;");

        $sidebar->addWidget($list, 'filter_semester');
    }

        /**
     * Adds the studiengangteil selector to the sidebar
     */
    private function getStgteilSelector($institut_id = null)
    {
        $institut_id = $institut_id ?: $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
        $stgteile = StudiengangTeil::getAllEnriched('fach_name', 'ASC', ['mvv_fach_inst.institut_id' => $institut_id]);
        $list = new SelectWidget(_('Studiengangteil'), $this->url_for('admin/courses/set_selection'), 'stgteil_select');
        if (!$institut_id || $institut_id === 'all') {
            $list->addElement(new SelectElement('', _('Wählen Sie eine Einrichtung') ), 'stgteil_select-all');
        } elseif (count($stgteile) === 0) {
                $list->addElement(new SelectElement('', _('Keine Studiengangteile zu der gewählten Einrichtung') ), 'stgteil_select-all');
        } else {
            $list->addElement(new SelectElement('', _('Alle')), 'stgteil_select-all');
        }
        foreach ($stgteile as $stgteil) {
            $list->addElement(new SelectElement(
                $stgteil->id,
                $stgteil->getDisplayName(),
                $stgteil->id === $GLOBALS['user']->cfg->MY_COURSES_SELECTED_STGTEIL
            ), 'stgteil_select-' . $stgteil->id);
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({stgteil: $(this).find('select').val()}); return false;");
        return $list;
    }


    /**
     * Adds HTML-Selector to the sidebar
     * @param null $selected_action
     */
    private function setActionsWidget()
    {
        $actions = $this->getActions();
        $sidebar = Sidebar::Get();
        $list = new SelectWidget(_('Aktionsbereichauswahl'), $this->url_for('admin/courses/set_action_type'), 'action_area');

        foreach ($actions as $index => $action) {
            $list->addElement(new SelectElement(
                $index,
                $action['name'],
                $GLOBALS['user']->cfg->MY_COURSES_ACTION_AREA == $index),
                'action-aria-' . $index
            );
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.App.changeActionArea($(this).find('select').val()); return false;");
        $sidebar->addWidget($list, 'editmode');
    }


    /**
     * Returns a course type widthet depending on all available courses and theirs types
     * @param string $selected
     * @param array $params
     */
    private function setCourseTypeWidget()
    {
        $sidebar = Sidebar::get();
        $this->url = $this->url_for('admin/courses/set_course_type');
        $this->types = [];
        $this->selected = $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER;

        $list = new SelectWidget(
            _('Veranstaltungstypfilter'),
            $this->url_for('admin/courses/set_course_type'),
            'course_type'
        );
        $list->addElement(new SelectElement(
            '', _('Alle'), !$GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER
        ), 'course-type-all');
        foreach ($GLOBALS['SEM_CLASS'] as $class_id => $class) {
            if ($class['studygroup_mode']) {
                continue;
            }

            $element = new SelectElement(
                $class_id,
                $class['name'],
                $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER === (string)$class_id
            );
            $list->addElement(
                $element->setAsHeader(),
                'course-type-' . $class_id
            );

            foreach ($class->getSemTypes() as $id => $result) {
                $element = new SelectElement(
                    $class_id . '_' . $id,
                    $result['name'],
                    $GLOBALS['user']->cfg->MY_COURSES_TYPE_FILTER === $class_id . '_' . $id
                );
                $list->addElement(
                    $element->setIndentLevel(1),
                    'course-type-' . $class_id . '_' . $id
                );
            }
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({course_type: $(this).find('select').val()}); return false;");
        $sidebar->addWidget($list, 'filter-course-type');
    }

    /**
     * Returns a widget to selected a specific teacher
     * @param array $teachers
     */
    private function getTeacherWidget($institut_id = null)
    {
        $institut_id = $institut_id ?: $GLOBALS['user']->cfg->MY_INSTITUTES_DEFAULT;
        $teachers = DBManager::get()->fetchAll("
                SELECT auth_user_md5.*, user_info.*
                FROM auth_user_md5
                    LEFT JOIN user_info ON (auth_user_md5.user_id = user_info.user_id)
                    INNER JOIN user_inst ON (user_inst.user_id = auth_user_md5.user_id)
                    INNER JOIN Institute ON (Institute.Institut_id = user_inst.Institut_id)
                WHERE (Institute.Institut_id = :institut_id OR Institute.fakultaets_id = :institut_id)
                    AND auth_user_md5.perms = 'dozent'
                ORDER BY auth_user_md5.Nachname ASC, auth_user_md5.Vorname ASC
            ", [
                'institut_id' => $institut_id
            ],
            function ($data) {
                $ret['user_id'] = $data['user_id'];
                unset($data['user_id']);
                $ret['fullname'] = User::build($data)->getFullName("full_rev");
                return $ret;
            }
        );


        $list = new SelectWidget(_('Lehrendenfilter'), $this->url_for('admin/courses/index'), 'teacher_filter');
        if (!$institut_id || $institut_id === 'all') {
            $list->addElement(new SelectElement('', _('Wählen Sie eine Einrichtung') ), 'teacher_filter-all');
        } elseif (count($teachers) === 0) {
            $list->addElement(new SelectElement('', _('Keine Lehrenden in der gewählten Einrichtung') ), 'teacher_filter-all');
        } else {
            $list->addElement(new SelectElement('', _('Alle')), 'teacher_filter-all');
        }

        foreach ($teachers as $teacher) {
            $list->addElement(new SelectElement(
                $teacher['user_id'],
                $teacher['fullname'],
                $GLOBALS['user']->cfg->ADMIN_COURSES_TEACHERFILTER === $teacher['user_id']
            ), 'teacher_filter-' . $teacher['user_id']);
        }
        $list->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({teacher_filter: $(this).find('select').val()}); return false;");
        return $list;
    }

    /**
     * Adds a search widget to the sidebar
     */
    private function setSearchWiget()
    {
        $sidebar = Sidebar::Get();
        $search = new SearchWidget(URLHelper::getURL('dispatch.php/admin/courses'));
        $search->addNeedle(
            _('Freie Suche'),
            'search',
            true,
            null,
            '',
            $GLOBALS['user']->cfg->ADMIN_COURSES_SEARCHTEXT
        );
        $search->setOnSubmitHandler("STUDIP.AdminCourses.App.changeFilter({search: $(this).find('input').val()}); return false;");
        $sidebar->addWidget($search, 'filter_search');
    }

    /**
     * Returns the filter configuration.
     *
     * @return array containing the filter configuration
     */
    private function getFilterConfig(): array
    {
        $available_filters = array_keys($this->getViewFilters());

        $temp = $GLOBALS['user']->cfg->MY_COURSES_ADMIN_VIEW_FILTER_ARGS;
        if ($temp) {
            $config = json_decode($temp, true);
            if (!is_array($config)) {
                $config = [];
            }

            $config = array_intersect($config, $available_filters);
        } else {
            $config = [];
        }

        if (!$config) {
            $config = $this->setFilterConfig([
                'number', 'name', 'semester', 'institute', 'teachers'
            ]);
        }

        return array_values($config);
    }

    /**
     * Sets the filter configuration.
     *
     * @param Array $config Filter configuration
     * @return array containing the filter configuration
     */
    private function setFilterConfig($config)
    {
        $config = $config ?: array_keys($this->getViewFilters());
        $GLOBALS['user']->cfg->store('MY_COURSES_ADMIN_VIEW_FILTER_ARGS', json_encode($config));

        return $config;
    }

    /**
     * Returns the default element configuration.
     *
     * @return array containing the default element configuration
     */
    private function getActiveElementsDefault()
    {
        return [
            'search' => true,
            'institute' => true,
            'semester' => true,
            'stgteil' => true,
            'courseType' => true,
            'teacher' => true,
            'viewFilter' => true
        ];
    }

    /**
     * Returns the active element configuration of the current user.
     *
     * @return array containing the active element configuration
     */
    private function getActiveElements()
    {
        $active_elements = $GLOBALS['user']->cfg->ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS;

        if ($active_elements) {
            return json_decode($active_elements, true);
        } else {
            return $this->getActiveElementsDefault();
        }
    }

    /**
     * Sets the active element configuration for the current user.
     *
     * @param Array $active_elements element configuration
     */
    private function setActiveElements($active_elements)
    {
        if ($active_elements == $this->getActiveElementsDefault()) {
            $GLOBALS['user']->cfg->delete('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS');
        } else {
            $GLOBALS['user']->cfg->store('ADMIN_COURSES_SIDEBAR_ACTIVE_ELEMENTS', json_encode($active_elements));
        }
    }
}
