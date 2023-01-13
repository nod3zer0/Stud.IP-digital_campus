<?php

require_once __DIR__.'/../courseware_controller.php';

use Courseware\StructuralElement;
use Courseware\Unit;

class Contents_CoursewareController extends CoursewareController
{
    /**
     * Callback function being called before an action is executed.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args): void
    {
        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.Courseware'); // set keyword for new help

        PageLayout::setTitle(_('Courseware'));

        $this->user = $GLOBALS['user'];
    }

    /**
     * Entry point of the controller that displays the courseware projects overview
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function index_action(): void
    {
        Navigation::activateItem('/contents/courseware/shelf');
        $this->user_id = $GLOBALS['user']->id;
        $this->setShelfSidebar();

        $this->licenses = $this->getLicenses();
    }

    private function setShelfSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
        SkipLinks::addIndex(_('Aktionen'), 'courseware-action-widget', 21);
        $sidebar->addWidget(new VueWidget('courseware-import-widget'));
    }

    /**
     * Show Courseware of current user
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function courseware_action($unit_id = null): void
    {
        global $perm, $user;

        $this->user_id = $user->id;
        /** @var array<mixed> $last */
        $last = UserConfig::get($this->user_id)->getValue('COURSEWARE_LAST_ELEMENT');

        if ($unit_id === null) {
            $this->redirectToFirstUnit('user', $this->user_id, $last);

            return;
        }

        $this->entry_element_id = null;
        $this->unit_id = null;
        $unit = Unit::find($unit_id);
        if (isset($unit)) {
            $this->setEntryElement('user', $unit, $last, $this->user_id);
            Navigation::activateItem('/contents/courseware/courseware');
            $this->licenses = $this->getLicenses();
            $this->setCoursewareSidebar();
        }
    }

    /**
     * Show users bookmarks
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function bookmarks_action(): void
    {
        Navigation::activateItem('/contents/courseware/bookmarks');
        $this->user_id = $GLOBALS['user']->id;
        $this->setBookmarkSidebar();
    }

    /**
     * Show users releases
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */

    public function releases_action(): void
    {
        Navigation::activateItem('/contents/courseware/releases');
        $this->user_id = $GLOBALS['user']->id;
    }

    private function setBookmarkSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $views = new TemplateWidget(
            _('Filter'),
            $this->get_template_factory()->open('contents/courseware/bookmark_filter_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-bookmark-filter-widget');
    }

    /**
     * displays coursewares in courses
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function courses_overview_action($action = false, $widgetId = null): void
    {
        Navigation::activateItem('/contents/courseware/courses_overview');

        $sidebar = Sidebar::get();
        $semester_widget = new SemesterSelectorWidget(
            $this->url_for('contents/courseware/courses_overview')
        );
        $semester_widget->includeAll();
        $sidebar->addWidget($semester_widget);

        $this->user_id = $GLOBALS['user']->id;

        $sem_key = Request::get('semester_id');
        if ($sem_key === '0' || $sem_key === null) {
            $sem_key = 'all';
            $this->all_semesters = true;
            $this->semesters = Semester::getAll();
        } else {
            $this->all_semesters = false;
            $this->semesters = [Semester::find($sem_key)];
        }
        usort($this->semesters, function ($a, $b) {
            if ($a->beginn === $b->beginn) {
                return 0;
            }
            return ($a->beginn > $b->beginn) ? -1 : 1;
        });

        $this->sem_courses  = $this->getCoursewareCourses($sem_key);
    }

    /**
     * Return list of coursewares grouped by semester_id
     *
     * @param  string $sem_key  currently selected semester or all (for all semesters)
     *
     * @return array
     */
    private function getCoursewareCourses($sem_key): array
    {
        $this->current_semester = Semester::findCurrent();

        $courses = Course::findThru($this->user_id, [
            'thru_table'        => 'seminar_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'seminar_id',
            'assoc_foreign_key' => 'seminar_id'
        ]);

        if (Config::get()->DEPUTIES_ENABLE) {
            $deputy_courses = Deputy::findDeputyCourses($GLOBALS['user']->id)->pluck('course');
            if (!empty($deputy_courses)) {
                $courses = array_merge($courses, $deputy_courses);
            }
        }

        $courses = new SimpleCollection($courses);

        if (!Config::get()->MY_COURSES_ENABLE_STUDYGROUPS) {
            $courses = $courses->filter(function ($a) {
                return !$a->isStudygroup();
            });
        }

        if ($sem_key != 'all') {
            $semester = Semester::find($sem_key);

            $courses = $courses->filter(function ($a) use ($semester) {
                if ($a->isInSemester($semester)) {
                    return true;
                }
                return false;
            });

            $coursewares = [];

            foreach ($courses as $course) {
                $element = StructuralElement::getCoursewareCourse($course->id);
                if (!empty($element) && $this->isCoursewareEnabled($course->id)) {
                    $element['payload'] = json_decode($element['payload'], true);
                    $coursewares[] = $element;
                }
            }

            if (empty($coursewares)) {
                return [];
            }

            return [$semester->id => [
                'semester_name' => $semester->name,
                'coursewares'   => $coursewares
            ]];
        } else {
            $all_semesters    = Semester::getAll();
            $sem_courses      = [];

            foreach ($courses as $course) {
                $element = StructuralElement::getCoursewareCourse($course->id);
                if (!empty($element) && $this->isCoursewareEnabled($course->id)) {
                    $element['payload'] = json_decode($element['payload'], true);

                    if ($course->duration_time == -1) {
                        $sem_courses[$this->current_semester->id]['coursewares'][] = $element;
                    } else {
                        $end_semester = $course->getEndSemester();
                        $sem_courses[$end_semester->id]['coursewares'][] = $element;
                    }
                }
            }

            return $sem_courses;
        }
    }

    /**
     * Returns true if the courseware module is enabled for the passed course
     *
     * @param  string  $course_id  the course to check
     * @return boolean             true if courseware is enabled, false otherwise
     */
    private function isCoursewareEnabled($course_id): bool
    {
        $studip_module = PluginManager::getInstance()->getPlugin('CoursewareModule');

        if (!$studip_module || !$studip_module->isActivated($course_id)) {
            return false;
        }

        return true;
    }


    private function getProjects($purpose): array
    {
        $elements = StructuralElement::findProjects($this->user->id, $purpose);
        foreach($elements as &$element) {
            $element['payload'] = json_decode($element['payload'], true);
        }

        return $elements;
    }


    public function pdf_export_action($element_id, $with_children): void
    {
        $element = \Courseware\StructuralElement::findOneById($element_id);

        $this->render_pdf($element->pdfExport($this->user, $with_children), trim($element->title).'.pdf');
    }

    /**
     * To display the shared courseware
     *
     * @param string $entry_element_id the shared struct element id
     */
    public function shared_content_courseware_action($entry_element_id): void
    {
        global $perm, $user;

        $navigation = new Navigation(_('Geteiltes Lernmaterial'), 'dispatch.php/contents/courseware/shared_content_courseware/' . $entry_element_id);
        Navigation::addItem('/contents/courseware/shared_content_courseware', $navigation);
        Navigation::activateItem('/contents/courseware/shared_content_courseware');

        $this->entry_element_id = $entry_element_id;

        $struct = \Courseware\StructuralElement::findOneBySQL(
            "id = ? AND range_type = 'user'",
            [$this->entry_element_id]
        );

        if (!$struct) {
            throw new Trails_Exception(404, _('Der geteilte Inhalt kann nicht gefunden werden.'));
        }

        if (!$struct->canRead($user) && !$struct->canEdit($user)) {
            throw new AccessDeniedException();
        }

        $this->user_id = $struct->owner_id;

        $this->licenses = $this->getLicenses();

        $this->oer_enabled = Config::get()->OERCAMPUS_ENABLED && $perm->have_perm(Config::get()->OER_PUBLIC_STATUS);

        $this->setCoursewareSidebar();
    }
}
