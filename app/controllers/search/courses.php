<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author   Rasmus Fuhse <fuhse@data-quest.de>
 * @license  http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category Stud.IP
 * @since    3.1
 */

class Search_CoursesController extends AuthenticatedController
{

    /**
     * @var string Holds the URL parameter with selected navigation option
     */
    private $nav_option = null;

    public function before_filter(&$action, &$args)
    {
        $this->allow_nobody = Config::get()->COURSE_SEARCH_IS_VISIBLE_NOBODY;

        parent::before_filter($action, $args);

        PageLayout::setHelpKeyword('Basis.VeranstaltungenAbonnieren');

        $this->type = Request::option('type', 'semtree');
        $this->show_as = Request::option('show_as', 'list');
        if (!in_array($this->show_as, ['list', 'table'])) {
            $this->show_as = 'list';
        }
        $this->semester = Request::option('semester', Semester::findCurrent()->id);
        $this->semClass = Request::int('semclass', 0);
        $this->search = Request::get('search', '');
    }

    public function index_action()
    {
        $nodeClass = '';
        if (Request::option('type', 'semtree') === 'semtree') {
            Navigation::activateItem('/search/courses/semtree');
            $nodeClass = StudipStudyArea::class;
            $this->treeTitle = _('Studienbereiche');
            $this->breadcrumbIcon = 'literature';
            $this->editUrl = $this->url_for('studyarea/edit');
        } else if (Request::option('type', 'semtree') === 'rangetree') {
            Navigation::activateItem('/search/courses/rangetree');
            $nodeClass = RangeTreeNode::class;
            $this->treeTitle = _('Einrichtungen');
            $this->breadcrumbIcon = 'institute';
            $this->editUrl = $this->url_for('rangetree/edit');
        }
        $this->startId = Request::option('node_id', $nodeClass . '_root');

        $this->setupSidebar();
    }

    public function export_results_action()
    {
        $sem_browse_obj = new SemBrowse();
        $tmpfile = basename($sem_browse_obj->create_result_xls());
        if ($tmpfile) {
            $this->redirect(FileManager::getDownloadURLForTemporaryFile(
                    $tmpfile, _('ErgebnisVeranstaltungssuche.xls'), 4));
        } else {
            $this->render_nothing();
        }
    }

    private function setupSidebar()
    {
        $sidebar = Sidebar::Get();

        $semWidget = new SemesterSelectorWidget(URLHelper::getURL('', ['type' => $this->type, 'semclass' => $this->semClass]), 'semester');
        $semWidget->includeAll(false);
        $semWidget->setId('semester-selector');
        $semWidget->setSelection($this->semester);
        $sidebar->addWidget($semWidget);

        $params = [
            'type' => $this->type
        ];
        if ($this->semClass !== 0) {
            $params['semclass'] = $this->semClass;
        }
        if ($this->semester !== '') {
            $params['semester'] = $this->semester;
        }
        if ($this->search !== '') {
            $params['search'] = $this->search;
        }
        $classWidget = $sidebar->addWidget(new SelectWidget(
            _('Veranstaltungskategorie'),
            URLHelper::getURL('', $params),
            'semclass'
        ));
        $classWidget->setId('semclass-selector');
        $classWidget->addElement(new SelectElement(0, _('Alle')));
        foreach (SemClass::getClasses() as $class) {
            if (!$class['studygroup_mode']) {
                $classWidget->addElement(new SelectElement(
                    $class['id'],
                    $class['name'],
                    $this->semClass == $class['id']
                ));
            }
        }

        $sidebar->addWidget(new VueWidget('search-widget'));
        $sidebar->addWidget(new VueWidget('export-widget'));

        $views = new ViewsWidget();
        $views->addLink(
            _('Als Liste'),
            $this->url_for('search/courses', array_merge($params, ['show_as' => 'list']))
        )->setActive($this->show_as === 'list');
        $views->addLink(
            _('Als Tabelle'),
            $this->url_for('search/courses', array_merge($params, ['show_as' => 'table']))
        )->setActive($this->show_as === 'table');
        $sidebar->addWidget($views);
    }
}
