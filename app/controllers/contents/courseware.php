<?php

use \Courseware\StructuralElement;

class Contents_CoursewareController extends AuthenticatedController
{
    /**
     * Callback function being called before an action is executed.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
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
    public function index_action($action = false, $widgetId = null)
    {
        Navigation::activateItem('/contents/courseware/projects');
        $this->setProjectsSidebar($action);
        $this->courseware_root = StructuralElement::getCoursewareUser($this->user->id);
        if (!$this->courseware_root) {
            // create initial courseware dataset
            $new = StructuralElement::createEmptyCourseware($this->user->id, 'user');
            $this->courseware_root = $new->getRoot();
        }

        $this->elements = $this->getProjects('all');
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
    public function courseware_action($action = false, $widgetId = null)
    {
        global $perm;

        Navigation::activateItem('/contents/courseware/courseware');
        $this->user_id = $GLOBALS['user']->id;

        $this->setCoursewareSidebar();

        $last = UserConfig::get($this->user_id)->getValue('COURSEWARE_LAST_ELEMENT');

        if ($last[$this->user_id]) {
            $this->entry_element_id = $last['global'];
            $struct = \Courseware\StructuralElement::findOneBySQL(
                "id = ? AND range_id = ? AND range_type = 'user'",
                [$this->entry_element_id, $this->user_id]
            );
        }

        // load courseware for current user
        if (!$this->entry_element_id || !$struct || !$struct->canRead($GLOBALS['user'])) {
            $user =  User::find($this->user_id);

            if (!$user->courseware) {
                // create initial courseware dataset
                StructuralElement::createEmptyCourseware($this->user_id, 'user');
            }

            $this->entry_element_id = $user->courseware->id;
        }

        $last[$this->user_id] = $this->entry_element_id;
        UserConfig::get($this->user_id)->store('COURSEWARE_LAST_ELEMENT', $last);

        $this->licenses = array();
        $sorm_licenses = License::findBySQL("1 ORDER BY name ASC");
        foreach($sorm_licenses as $license) {
            array_push($this->licenses, $license->toArray());
        }
        $this->licenses = json_encode($this->licenses);

        $this->oer_enabled = Config::get()->OERCAMPUS_ENABLED && $perm->have_perm(Config::get()->OER_PUBLIC_STATUS);
    }

    private function setCoursewareSidebar()
    {
        $sidebar = \Sidebar::Get();
        $actions = new TemplateWidget(
            _('Aktionen'),
            $this->get_template_factory()->open('course/courseware/action_widget')
        );
        $sidebar->addWidget($actions)->addLayoutCSSClass('courseware-action-widget');

        $views = new \TemplateWidget(
            _('Ansichten'),
            $this->get_template_factory()->open('course/courseware/view_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-view-widget');


    }

    /**
     * displays the courseware manager
     *
     * @param string $action
     * @param string $widgetId
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function courseware_manager_action($action = false, $widgetId = null)
    {
        Navigation::activateItem('/contents/courseware/courseware_manager');

        $this->user_id = $GLOBALS['user']->id;
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

    public function bookmarks_action($action = false, $widgetId = null)
    {
        Navigation::activateItem('/contents/courseware/bookmarks');
        $this->bookmarks = array();
        $cw_bookmarks =  Courseware\Bookmark::findUsersBookmarks($this->user->id);
        foreach($cw_bookmarks as $bookmark) {
            $bm = array();
            $bm['bookmark'] = $bookmark;
            $element = Courseware\StructuralElement::find($bookmark->element_id);
            if(empty($element)) {
                continue;
            }
            $element['payload'] = json_decode($element['payload'], true);
            $bm['element'] = $element;
            if ($element->range_type === 'course') {
                $bm['url'] = URLHelper::getURL('dispatch.php/course/courseware/?cid='.$element['range_id'].'#/structural_element/'.$element['id']);
                $bm['course'] = Course::find($element['range_id']);
            }
            if ($element->range_type === 'user' && $element->range_id === $this->user->id) {
                $bm['url'] = URLHelper::getURL('dispatch.php/contents/courseware/courseware#/structural_element/'.$element['id']);
                $bm['user'] = $this->user;
            }

            array_push($this->bookmarks, $bm);
        }
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
    public function courses_overview_action($action = false, $widgetId = null)
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
        $params = [
            'order_by'            => null,
            'order'               => 'asc',
            'studygroups_enabled' => Config::get()->MY_COURSES_ENABLE_STUDYGROUPS,
            'deputies_enabled'    => Config::get()->DEPUTIES_ENABLE,
        ];

        $this->sem_courses  = $this->getCoursewareCourses($sem_key);
    }

    /**
     * Return list of coursewares grouped by semester_id
     *
     * @param  string $sem_key  currently selected semester or all (for all semesters)
     *
     * @return array
     */
    private function getCoursewareCourses($sem_key)
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
    private function isCoursewareEnabled($course_id)
    {
        $studip_module = PluginManager::getInstance()->getPlugin('CoursewareModule');

        if (!$studip_module || !$studip_module->isActivated($course_id)) {
            return false;
        }

        return true;
    }


    private function getProjects($purpose)
    {
        $elements = StructuralElement::findProjects($this->user->id, $purpose);
        foreach($elements as &$element) {
            $element['payload'] = json_decode($element['payload'], true);
        }

        return $elements;
    }

    public function create_project_action($action = false, $widgetId = null)
    {
        PageLayout::setTitle(_('Neues Lernmaterial'));

        if (!Request::submitted('create_project')) {
            return;
        }

        CSRFProtection::verifyUnsafeRequest();

        $this->user_id = $GLOBALS['user']->id;

        $title = Request::get('title');
        $projectType = Request::get('project_type');
        $description = Request::get('description');
        $color = Request::get('color');
        $licenseType = Request::get('license_type');
        $requiredTime = Request::get('required_time');
        $difficultyStart = Request::get('difficulty_start');
        $difficultyEnd = Request::get('difficulty_end');


        $currentDate = time();

        $structural_element = new StructuralElement();

        $structural_element->title = $title;
        $structural_element->purpose = $projectType;

        $structural_element->owner_id = $this->user_id;
        $structural_element->editor_id = $this->user_id;

        $structural_element->release_date = "";
        $structural_element->withdraw_date = "";

        $structural_element->range_id = $this->user_id;
        $structural_element->range_type = 'user';
        $structural_element->parent_id = StructuralElement::getCoursewareUser($this->user_id)->id;

        $structural_element->payload = json_encode(array(
            'description'=> $description,
            'color' => $color,
            'required_time' => $requiredTime,
            'license_type' => $licenseType,
            'difficulty_start' => $difficulty_start,
            'difficulty_end' => $difficulty_end
        ));

        $structural_element->mkdate = $currentDate;
        $structural_element->chdate = $currentDate;

        $structural_element->store();

        // set image
        if ($_FILES['previewfile'] && $_FILES['previewfile']['name']) {
            $coursewareInstance = new Courseware\Instance($structural_element);
            $publicFolder = Courseware\Filesystem\PublicFolder::findOrCreateTopFolder($coursewareInstance);
            $fileRef = $this->handleUpload($publicFolder, $structural_element);
            $structural_element->image_id = $fileRef->id;
            $structural_element->store();
        }

        $this->redirect('contents/courseware/index');
    }

    private function handleUpload(Courseware\Filesystem\PublicFolder $folder, StructuralElement $structuralElement)
    {
        $file = $_FILES['previewfile'];
        $upload = [
            'tmp_name' => [$file['tmp_name']],
            'name'     => [$file['name']],
            'size'     => [$file['size']],
            'type'     => [$file['type']],
            'error'    => [$file['error']]
        ];

        $uploaded = FileManager::handleFileUpload(
            $upload,
            $folder
        );

        if ($uploaded['error']) {
            throw new RuntimeException(implode("\n", $uploaded['error']));
        }

        if (count($uploaded['files'])) {
            return $uploaded['files'][0];
        }

        throw new RuntimeException('Could not create preview image.');
    }

    private function setProjectsSidebar($action)
    {
        $sidebar = Sidebar::Get();
        $actions = new ActionsWidget();
        $actions->addLink(_('Neues Lernmaterial anlegen'), $this->url_for('contents/courseware/create_project'), Icon::create('add', 'clickable'))->asDialog('size=700');
        $sidebar->addWidget($actions);
    }
}
