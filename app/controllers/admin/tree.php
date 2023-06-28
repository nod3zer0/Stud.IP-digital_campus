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
        $GLOBALS['perm']->check('admin');
        //set the page title with the area of Stud.IP:
        PageLayout::setTitle(_('Veranstaltungszuordnungen bearbeiten'));
        Navigation::activateItem('/browse/my_courses/list');

        $GLOBALS['perm']->check('admin');

        // check the assign_semtree array and extract the relevant course IDs:
        $courseIds = Request::optionArray('assign_semtree');

        $order = Config::get()->IMPORTANT_SEMNUMBER
            ? "ORDER BY `start_time` DESC, `VeranstaltungsNummer`, `Name`"
            : "ORDER BY `start_time` DESC,  `Name`";
        $this->courses = Course::findMany($courseIds, $order);

        $this->return = Request::get('return');

        // check if at least one course was selected (this can only happen from admin courses overview):
        if (!$courseIds) {
            PageLayout::postWarning('Es wurde keine Veranstaltung gewählt.');
            $this->relocate('admin/courses');
        }
    }

    public function assign_courses_action($class_id)
    {
        $GLOBALS['perm']->check('root');
        $data = $this->checkClassAndId($class_id);
        $GLOBALS['perm']->check('admin');

        $this->search = QuickSearch::get('courses[]', new StandardSearch('Seminar_id'))->withButton();
        $this->node = $data['id'];
    }

    /**
     * Store (de-)assignments from courses to sem_tree nodes.
     * @return void
     */
    public function do_batch_assign_action()
    {
        $GLOBALS['perm']->check('admin');
        $astmt = DBManager::get()->prepare("INSERT IGNORE INTO `seminar_sem_tree` VALUES (:course, :node)");
        $dstmt = DBManager::get()->prepare(
            "DELETE FROM `seminar_sem_tree` WHERE `seminar_id` IN (:courses) AND `sem_tree_id` = :node");

        $success = true;
        // Add course assignments to the specified nodes.
        foreach (Request::optionArray('courses') as $course) {
            foreach (Request::optionArray('add_assignments') as $a) {
                $success = $astmt->execute(['course' => $course, 'node' => $a]);
            }
        }

        // Remove course assignments from the specified nodes.
        foreach (Request::optionArray('delete_assignments') as $d) {
            $success = $dstmt->execute(['courses' => Request::optionArray('courses'), 'node' => $d]);
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
