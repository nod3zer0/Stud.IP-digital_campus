<?php

class Admin_TreeController extends AuthenticatedController
{
    public function rangetree_action()
    {
        $GLOBALS['perm']->check('root');
        Navigation::activateItem('/admin/locations/range_tree');
        PageLayout::setTitle(_('Einrichtungshierarchie bearbeiten'));
        $this->startId = Request::get('node_id', 'RangeTreeNode_root');
        $this->semester = Request::option('semester', Semester::findCurrent()->id);
        $this->classname = RangeTreeNode::class;
        $this->setupSidebar();
    }

    public function semtree_action()
    {
        $GLOBALS['perm']->check('root');
        Navigation::activateItem('/admin/locations/sem_tree');
        PageLayout::setTitle(_('Veranstaltungshierarchie bearbeiten'));
        $this->startId = Request::get('node_id', 'StudipStudyArea_root');
        $this->semester = Request::option('semester', Semester::findCurrent()->id);
        $this->classname = StudipStudyArea::class;
        $this->setupSidebar();
    }

    /**
     * Edit the given node.
     *
     * @param string $class_id concatenated classname and node id
     * @return void
     */
    public function edit_action(string $class_id)
    {
        $GLOBALS['perm']->check('root');
        PageLayout::setTitle(_('Eintrag bearbeiten'));

        $data = $this->checkClassAndId($class_id);
        $this->node = $data['classname']::getNode($data['id']);
        $parent = $data['classname']::getNode($this->node->parent_id);

        $this->treesearch = QuickSearch::get(
            'parent_id',
            new TreeSearch($data['classname'] === StudipStudyArea::class ? 'sem_tree_id' : 'range_tree_id')
        )->withButton();
        $this->treesearch->defaultValue($parent->id, $parent->getName());

        if ($data['classname'] === RangeTreeNode::class) {
            $this->instsearch = QuickSearch::get(
                'studip_object_id',
                new StandardSearch('Institut_id')
            )->withButton();
            if ($this->node->studip_object_id) {
                $this->instsearch->defaultValue($this->node->studip_object_id, $this->node->institute->name);
            }
        }

        $this->from = Request::get('from');
    }

    /**
     * Create a new child node of the given parent.
     *
     * @param string $class_id concatenated classname and parent id
     * @return void
     */
    public function create_action(string $class_id)
    {
        $GLOBALS['perm']->check('root');
        PageLayout::setTitle(_('Neuen Eintrag anlegen'));

        $data = $this->checkClassAndId($class_id);

        $this->node = new $data['classname']();
        $this->node->parent_id = $data['id'];
        $parent = $data['classname']::getNode($data['id']);

        $this->treesearch = QuickSearch::get(
            'parent_id',
            new TreeSearch(get_class($this->node) === StudipStudyArea::class ? 'sem_tree_id' : 'range_tree_id')
        )->withButton();
        $this->treesearch->defaultValue($parent->id, $parent->getName());

        $this->instsearch = QuickSearch::get(
            'studip_object_id',
            new StandardSearch('Institut_id')
        )->withButton();

        $this->from = Request::get('from');
    }

    /**
     * Delete the given child node.
     *
     * @param string $class_id concatenated classname and node id
     * @return void
     */
    public function delete_action(string $class_id)
    {
        $GLOBALS['perm']->check('root');
        $data = $this->checkClassAndId($class_id);

        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }
        $node = $data['classname']::getNode($data['id']);

        if ($node) {
            $node->delete();
        } else {
            $this->set_status(404);
        }

        $this->render_nothing();
    }

    /**
     * Store the given node.
     *
     * @param string $classname
     * @param string $node_id
     * @return void
     */
    public function store_action(string $classname, string $node_id = '')
    {
        $GLOBALS['perm']->check('root');
        CSRFProtection::verifyUnsafeRequest();

        $node = new $classname($node_id);
        $node->parent_id = Request::option('parent_id');

        $parent = $classname::getNode(Request::option('parent_id'));
        $maxprio = max(array_map(
            function ($c) {
                return $c->priority;
            },
            $parent->getChildNodes()
        ));
        $node->priority = $maxprio + 1;

        if (Request::option('studip_object_id')) {
            $node->studip_object_id = Request::option('studip_object_id');
            $node->name = '';
        } else {
            $node->name = Request::get('name');
        }

        if ($classname === StudipStudyArea::class) {
            $node->info = Request::get('description');
            $node->type = Request::int('type');
        }

        if ($node->store() !== false) {
            Pagelayout::postSuccess(_('Die Daten wurden gespeichert.'));
        } else {
            Pagelayout::postError(_('Die Daten konnten nicht gespeichert werden.'));
        }

        $this->relocate(Request::get('from'));
    }

    public function sort_action($parent_id)
    {
        $GLOBALS['perm']->check('root');
        $data = $this->checkClassAndId($parent_id);

        $parent = $data['classname']::getNode($data['id']);
        $children = $parent->getChildNodes();

        $data = json_decode(Request::get('sorting'), true);

        foreach ($children as $child) {
            $child->priority = $data[$child->id];
            $child->store();
        }

        $this->render_nothing();
    }

    /**
     * (De-)assign several courses at once to a sem_tree node
     * @return void
     * @throws Exception
     */
    public function batch_assign_semtree_action()
    {
        if (!$GLOBALS['perm']->have_perm('admin')
                && !RolePersistence::isAssignedRole(User::findCurrent()->id, 'DedicatedAdmin')) {
            throw new AccessDeniedException();
        }

        //set the page title with the area of Stud.IP:
        PageLayout::setTitle(_('Veranstaltungszuordnungen bearbeiten'));
        Navigation::activateItem('/browse/my_courses/list');

        // check the assign_semtree array and extract the relevant course IDs:
        $courseIds = Request::optionArray('assign_semtree');

        $order = Config::get()->IMPORTANT_SEMNUMBER
            ? "ORDER BY `start_time` DESC, `VeranstaltungsNummer`, `Name`"
            : "ORDER BY `start_time` DESC,  `Name`";
        $this->courses = array_filter(
            Course::findMany($courseIds, $order),
            function (Course $course): bool {
                /*
                 * Check if sem_tree entries are allowed and may be changed and remove all courses
                 * where this is not the case.
                 */
                return !LockRules::Check($course->id, 'sem_tree', 'sem')
                    && $course->getSemClass()['bereiche'];
            }
        );

        $this->return = Request::get('return');

        // check if at least one course was selected (this can only happen from admin courses overview):
        if (count($this->courses) === 0) {
            PageLayout::postWarning('Es wurde keine Veranstaltung gewählt oder die Zuordnungen können ' .
                'nicht bearbeitet werden.');
            $this->relocate('admin/courses');
        }
    }

    /**
     * Store (de-)assignments from courses to sem_tree nodes.
     * @return void
     */
    public function do_batch_assign_action()
    {
        if (!$GLOBALS['perm']->have_perm('admin')
            && !RolePersistence::isAssignedRole(User::findCurrent()->id, 'DedicatedAdmin')) {
            throw new AccessDeniedException();
        }

        CSRFProtection::verifyUnsafeRequest();

        $success = true;
        $courses = Course::findMany(Request::optionArray('courses'));
        foreach ($courses as $course) {
            if ($GLOBALS['perm']->have_studip_perm('tutor', $course->id)) {
                $areas = $course->study_areas->pluck('sem_tree_id');
                $newAreas = array_merge($areas, Request::optionArray('add_assignments'));
                $delete = Request::optionArray('delete_assignments');
                $changed = array_diff($newAreas, $delete);
                // Set new areas for course if at least one area remains.
                if (count($changed) > 0) {
                    $course->setStudyAreas($changed);
                // Allow to remove all study areas only when there are modules.
                } else if ($course->getSemClass()['module'] && count(Lvgruppe::findBySeminar($course->id))) {
                    $course->setStudyAreas($changed);
                } else {
                    $success = false;
                }
            } else {
                $success = false;
            }
        }

        if ($success) {
            PageLayout::postSuccess(_('Die Zuordnungen wurden gespeichert.'));
        } else {
            PageLayout::postError(_('Die Zuordnungen konnten nicht vollständig gespeichert werden.'));
        }

        $this->relocate(Request::get('return', 'admin/courses'));
    }

    private function setupSidebar()
    {
        $sidebar = Sidebar::Get();

        $semWidget = new SemesterSelectorWidget($this->url_for(''), 'semester');
        $semWidget->includeAll(true);
        $semWidget->setId('semester-selector');
        $semWidget->setSelection($this->semester);
        $sidebar->addWidget($semWidget);

        if ($this->classname === StudipStudyArea::class) {
            $sidebar->addWidget(new VueWidget('assign-widget'));
        }
    }

    /**
     * CHeck a combination of class name and ID for validity: is this a StudipTreeNode subclass?
     * If yes, return the corresponding object.
     *
     * @param string $class_id class name and ID, separated by '_'
     * @return mixed
     */
    private function checkClassAndId($class_id)
    {
        list($classname, $id) = explode('_', $class_id);

        if (is_a($classname, StudipTreeNode::class, true)) {
            return [
                'classname' => $classname,
                'id' => $id
            ];
        }

        throw new InvalidArgumentException(
            sprintf('The given class "%s" does not implement the StudipTreeNode interface!', $classname)
        );

    }
}
