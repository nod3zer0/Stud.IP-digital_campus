<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     GPL2 or any later version
 * @since       3.5
 */

class Lvgruppen_LvgruppenController extends MVVController
{
    public $filter = [];
    public $semester_filter = null;
    private $show_sidebar_search = false;
    private $show_sidebar_filter = false;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->lvgruppe_id = '';

        // set navigation
        Navigation::activateItem($this->me . '/lvgruppen/lvgruppen');
        $this->filter = $this->sessGet('filter', []);
        // set the selected semester, if not set use selected semester from
        // my courses or the current semester
        $this->semester_filter = $this->sessGet(
            'semester_filter',
            $GLOBALS['user']->cfg->MY_COURSES_SELECTED_CYCLE ?: Semester::findCurrent()->id
        );
        $this->action = $action;
    }

    /**
     * show list of Lehrveranstaltungsgruppen
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung der Lehrveranstaltungsgruppen'));
        $this->initPageParams();
        $this->initSearchParams();

        $search_result = $this->getSearchResult('Lvgruppe');

        $lvg_ids = [];
        if (count($search_result) > 0) {
            $lvg_ids = $search_result;
        } else {
            if (!empty($_SESSION['mvv_filter_lvg_fach_id'])) {
                $lvg_ids = $this->findLvgIdsByFach($_SESSION['mvv_filter_lvg_fach_id']);
            }
            if (!empty($_SESSION['mvv_filter_lvg_abschluss_id'])) {
                if (count($lvg_ids) > 0) {
                    $lvg_ids = array_intersect(
                        $lvg_ids,
                        $this->findLvgIdsByAbschluss($_SESSION['mvv_filter_lvg_abschluss_id'])
                    );
                } else {
                    $lvg_ids = $this->findLvgIdsByAbschluss($_SESSION['mvv_filter_lvg_abschluss_id']);
                }
            }
        }

        // Nur LvGruppen an Modulen von verantwortlichen Einrichtungen an denen der User
        // eine Rolle hat
        $own_institutes = MvvPerm::getOwnInstitutes();
        if (empty($this->filter['mvv_modul_inst.institut_id'])) {
            $this->filter['mvv_modul_inst.institut_id'] = $own_institutes;
        } else if (count($own_institutes)) {
            $this->filter['mvv_modul_inst.institut_id'] = array_intersect(
                (array)$this->filter['mvv_modul_inst.institut_id'],
                $own_institutes
            );
        }

        if (is_array($this->filter['mvv_modul_inst.institut_id']) && count($this->filter['mvv_modul_inst.institut_id'])) {
            $filter = array_merge(
                [
                    'mvv_lvgruppe.lvgruppe_id' => $search_result,
                    'mvv_modul_inst.gruppe'    => 'hauptverantwortlich'
                ],
                (array)$this->filter
            );
        } else {
            $filter = array_merge(
                ['mvv_lvgruppe.lvgruppe_id' => $search_result],
                (array)$this->filter);
        }
        $this->sortby = $this->sortby ?: 'name';
        $this->order = $this->order ?: 'ASC';
        if ($this->semester_filter === 'no') {
            $filter['seminare.seminar_id'] = '__undefined__';
        }
        $author_sql = null;
        $this->lvgruppen = Lvgruppe::getAllEnriched(
            $this->sortby,
            $this->order,
            Lvgruppe::getFilterSql($filter, true, $author_sql),
            self::$items_per_page,
            self::$items_per_page * (($this->page ?: 1) - 1),
            $this->semester_filter
        );
        if (!empty($this->filter)) {
            $this->search_result['Lvgruppe'] = $this->lvgruppen->pluck('id');
        }

        $this->count = Lvgruppe::getCount($filter, $this->semester_filter);

        $helpbar = Helpbar::get();
        $widget = new HelpbarWidget();
        $widget->addElement(new WidgetElement(_('Auf diesen Seiten können Sie Lehrveranstaltungsgruppen verwalten.').'</br>'));
        $widget->addElement(new WidgetElement(_('Eine Lehrveranstaltungsgruppe kann aufgeklappt werden, um die Lehrveranstaltungen anzuzeigen, die dieser Gruppe bereits zugeordnet wurden.')));
        $helpbar->addWidget($widget);

        $this->show_sidebar_search = true;
        $this->show_sidebar_filter = true;

        $this->setSidebar();
        $sidebar = Sidebar::get();
        $widget  = new ExportWidget();

        $widget->addLink(_('Lehrveranstaltungsgruppen mit Zuordnungen exportieren'),
                $this->action_url('export_xls'),
                Icon::create('export'));

        if (Config::get()->MVV_ALLOW_CREATE_LVGRUPPEN_INDEPENDENTLY
                && MvvPerm::havePermCREATE('Lvgruppe')) {
            $widget->addLink(_('Neue LV-Gruppe anlegen'),
                $this->action_url('lvgruppe'),
                Icon::create('add'), ['data-dialog' => 'size=auto']);
        }

        $sidebar->addWidget($widget);
    }

    public function details_action($lvgruppe_id = null)
    {
        $this->lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$this->lvgruppe) {
            throw new Exception(_('Unbekannte LV-Gruppe'));
        }

        $this->display_semesters = [];
        if ($this->semester_filter !== 'all') {
            // show courses of the current and next semester
            $this->courses = $this->lvgruppe->getAllAssignedCourses(
                false,
                $this->semester_filter
            );
            $semester = Semester::find($this->semester_filter);
            if ($semester && $semester->isCurrent()) {
                $this->next_sem = Semester::findNext();
                $this->display_semesters[] = $this->next_sem;
                $this->courses = array_merge($this->courses,
                    $this->lvgruppe->getAllAssignedCourses(false, $this->next_sem->id)
                );
            }
            $this->current_sem = $semester;
            $this->display_semesters[] = $semester;
            // show only pathes to Studiengaenge valid in given semesters
            $this->set_trails_filter(
                end($this->display_semesters)->beginn,
                reset($this->display_semesters)->ende
            );
        } else {
            // show courses of all elapsed, current and next semesters
            $this->courses = $this->lvgruppe->getAllAssignedCourses();
            $this->next_sem = Semester::findNext();
            $this->current_sem = Semester::findCurrent();

            $this->display_semesters = array_reverse(Semester::getAll());
        }

        $this->sem_filter = $this->semester_filter;

        $this->trail_classes = [
            'Modulteil',
            'Modul',
            'StgteilAbschnitt',
            'StgteilVersion',
            'Studiengang',
            'Fachbereich'
        ];
        $this->trails = $this->lvgruppe->getTrails(
            $this->trail_classes, MvvTreeItem::TRAIL_SHOW_INCOMPLETE
        );

        if (!Request::isXhr()){
            $this->perform_relayed('index');
            return true;
        }
    }

    public function lvgruppe_action($lvgruppe_id = null)
    {
        $this->lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$this->lvgruppe) {
            $this->lvgruppe = new Lvgruppe();
            PageLayout::setTitle(_('Neue Lehrveranstaltungsgruppe anlegen'));
            $success_message = _('Die Lehrveranstaltungsgruppe "%s" wurde angelegt.');
            $this->headline = _('Neue Lehrveranstaltungsgruppe anlegen.');
        } else {
            PageLayout::setTitle(_('Lehrveranstaltungsgruppe bearbeiten'));
            $success_message = _('Die Lehrveranstaltungsgruppe "%s" wurde gespeichert.');
            $this->headline = sprintf(
                _('Lehrveranstaltungsgruppe "%s" bearbeiten.'),
                $this->lvgruppe->getDisplayName()
            );
        }
        $this->cancel_url = $this->action_url('index');
        $this->submit_url = $this->action_url('lvgruppe/' . $this->lvgruppe->getId());
        if (Request::submitted('store')) {
            CSRFProtection::verifyUnsafeRequest();
            $stored = false;
            $this->lvgruppe->name = trim(Request::get('name'));
            $this->lvgruppe->alttext = Request::i18n('alttext')->trim();
            try {
                $this->lvgruppe->verifyPermission();
                $stored = $this->lvgruppe->store();
            } catch (InvalidValuesException $e) {
                PageLayout::postError(htmlReady($e->getMessage()));
            }
            if ($stored !== false) {
                $this->sessSet('sortby', 'chdate');
                $this->sessSet('order', 'DESC');
                PageLayout::postSuccess(sprintf(
                    $success_message,
                    htmlReady($this->lvgruppe->getDisplayName())
                ));
                $this->relocate($this->action_url('index'));
                return;
            }
        }
    }

    public function delete_action($lvgruppe_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $lvgruppe = Lvgruppe::find($lvgruppe_id);
        if (!$lvgruppe) {
            throw new Exception(_('Unbekannte LV-Gruppe'));
        }
        $perm = MvvPerm::get($lvgruppe);
        if ($lvgruppe->isNew()) {
            PageLayout::postError(_('Die Lehrveranstaltungsgruppe kann nicht gelöscht werden (unbekannte Lehrveranstaltungsgruppe).'));
        } elseif (count($lvgruppe->courses) || count($lvgruppe->modulteile) || count($lvgruppe->archived_courses)) {
            PageLayout::postError(_('Die Lehrveranstaltungsgruppe kann nicht gelöscht werden, da sie mit Veranstaltungen oder Modulteilen verknüpft ist.'));
        } else {
            if ($perm->havePerm(MvvPerm::PERM_CREATE)) {
                $name = $lvgruppe->getDisplayName();
                $lvgruppe->delete();
                PageLayout::postSuccess(sprintf(
                    _('Die Lehrveranstaltungsgruppe "%s" wurde gelöscht.'),
                    htmlReady($name)
                ));
            } else {
                throw new AccessDeniedException();
            }
        }

        $this->redirect('lvgruppen/lvgruppen');
    }

    public function export_xls_action()
    {
        $this->initSearchParams();
        $this->initPageParams();

        $search_result = $this->getSearchResult('Lvgruppe');

        if (count($this->filter['mvv_modul_inst.institut_id'])) {
            $filter = array_merge(
                [
                    'mvv_lvgruppe.lvgruppe_id' => $search_result,
                    'mvv_modul_inst.gruppe'    => 'hauptverantwortlich'
                ],
                (array)$this->filter
            );
        } else {
            $filter = array_merge(
                ['mvv_lvgruppe.lvgruppe_id' => $search_result],
                (array)$this->filter
            );
        }

        $this->lvgruppen = Lvgruppe::getAllEnriched(
            $this->sortby,
            $this->order,
            $filter,
            null,
            null,
            $this->semester_filter
        );

        if ($this->semester_filter === 'all') {
            $semester = Semester::getAll();
            $this->set_trails_filter(
                end($semester)->beginn,
                reset($semester)->ende
            );
        } else {
            $semester = Semester::find($this->semester_filter);
            $this->set_trails_filter($semester->beginn, $semester->ende);
        }

        $this->response->add_header('Content-Description', 'File Transfer');
        $this->response->add_header('Content-Transfer-Encoding' , 'binary');
        $this->response->add_header('Content-type', 'application/vnd.ms-excel');
        $this->response->add_header('Content-Disposition', 'attachment; filename=lv_gruppen.xls');
        $this->response->add_header('Pragma', 'private');
        $this->render_template('lvgruppen/lvgruppen/export_xls', null);
    }

    /**
     * do the search
     */
    public function search_action()
    {
        $this->reset_search('Lvgruppe');
        $this->reset_page();
        $this->do_search(
            'Lvgruppe',
            trim(Request::get('lvgruppe_suche_parameter')),
            Request::option('lvgruppe_suche')
        );
        $this->redirect($this->action_url('index'));
    }

    /**
     * resets the search
     */
    public function reset_search_action()
    {
        $this->reset_search('Lvgruppe');
        $this->reset_page();
        $this->redirect($this->action_url('index'));
    }

    /**
     * Creates the sidebar widgets
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::get();

        if ($this->show_sidebar_search) {
            $this->sidebar_search();
        }
        if ($this->show_sidebar_filter) {
            $this->sidebar_filter();
        }
        $this->sidebar_rendered = true;
    }

    /**
     * adds the filter function to the sidebar
     */
    private function sidebar_filter()
    {
        $selected_fachbereich = $this->filter['mvv_modul_inst.institut_id'];

        $sidebar = Sidebar::get();

        $widget = new SelectWidget(
            _('Verwendet in Semester'),
            $this->action_url('set_filter', ['fachbereich_filter' => $selected_fachbereich]),
            'semester_filter'
        );

        $widget->addElement(
            new SelectElement('all', _('Alle'), $this->semester_filter === 'all'), 'sem_select-all'
        );
        $widget->addElement(
            new SelectElement('no', _('Nicht verwendet'), $this->semester_filter === 'no'), 'sem_select-no'
        );
        $semesters = array_reverse(Semester::getAll());
        foreach ($semesters as $semester) {
            $widget->addElement(
                new SelectElement(
                    $semester->semester_id, $semester->name,
                    $this->semester_filter === $semester->semester_id
                ),
                'select-' . $semester->name
            );
        }
        $sidebar->addWidget($widget, 'semester_filter');

        $perm_institutes = MvvPerm::getOwnInstitutes();
        if ($perm_institutes !== false) {
            $widget = new SelectWidget(
                _('Verwendet von Fachbereich'),
                $this->action_url('set_filter', ['semester_filter' => $this->semester_filter]),
                'fachbereich_filter'
            );

            $widget->class = 'institute-list';
            $widget->addElement(
                new SelectElement('select-none', _('Alle'), $selected_fachbereich === '')
            );
            $widget->addElement(
                new SelectElement('__undefined__', _('Nicht verwendet'), $selected_fachbereich === '__undefined__')
            );

            $institutes = Institute::getInstitutes();
            foreach ($institutes as $institute) {
                if (!(count($perm_institutes) === 0 || in_array($institute['Institut_id'], $perm_institutes))) {
                    continue;
                }

                $widget->addElement(
                    new SelectElement(
                        $institute['Institut_id'],
                        ($institute['is_fak'] ? '' : ' ') . $institute['Name'],
                        $institute['Institut_id'] === $selected_fachbereich
                    ),
                    'select-' . $institute['Name']
                );

            }

            $sidebar->addWidget($widget, 'fachbereich_filter');
        }

        $selected_fach = $_SESSION['mvv_filter_lvg_fach_id'] ?? '';
        $widget = new SelectWidget(
            _('Verwendet von Fach'),
            $this->action_url('set_filter', ['fach_filter' => $selected_fach]),
            'fach_filter'
        );
        $widget->class = 'nested-select';
        $widget->addElement(
            new SelectElement('select-none', _('Alle'), $selected_fach === '')
        );
        $faecher = Fach::findBySQL(' 1 ORDER BY `name`');
        foreach ($faecher as $fach) {
            $widget->addElement(
                new SelectElement(
                    $fach->id,
                    $fach->name,
                    $selected_fach === $fach->id
                ),
                'select-' . $fach->name
            );
        }
        $sidebar->addWidget($widget, 'fach_filter');

        $selected_abschluss = $_SESSION['mvv_filter_lvg_abschluss_id'] ?? '';
        $widget = new SelectWidget(
            _('Verwendet von Abschluss'),
            $this->action_url('set_filter', ['abschluss_filter' => $selected_abschluss]),
            'abschluss_filter'
        );
        $widget->class = 'nested-select';
        $widget->addElement(
            new SelectElement('select-none', _('Alle'), $selected_abschlussh === '')
        );
        $abschluesse = Abschluss::findBySQL(' 1 ORDER BY `name`');
        foreach ($abschluesse as $abschluss) {
            $widget->addElement(
                new SelectElement(
                    $abschluss->id,
                    $abschluss->name,
                    $selected_abschluss === $abschluss->id
                ),
                'select-' . $abschluss->name
            );
        }
        $sidebar->addWidget($widget, 'abschluss_filter');
    }

    /**
     * sets filter parameters and stores filters in session
     */
    public function set_filter_action()
    {
        // Zugeordnete Fachbereiche
        $this->filter['mvv_modul_inst.institut_id'] =
            mb_strlen(Request::get('fachbereich_filter')) ? Request::option('fachbereich_filter') : null;

        // Semester
        $this->semester_filter =
            mb_strlen(Request::get('semester_filter')) ? Request::option('semester_filter') : null;

        // Fach
        $_SESSION['mvv_filter_lvg_fach_id'] = Request::option('fach_filter', '');

        // Abschluss
        $_SESSION['mvv_filter_lvg_abschluss_id'] = Request::option('abschluss_filter', '');

        // store filter
        $this->reset_page();
        $this->sessSet('filter', $this->filter);
        $this->sessSet('semester_filter', $this->semester_filter);
        $this->redirect($this->action_url('index'));
    }

    public function reset_filter_action()
    {
        $_SESSION['mvv_filter_lvg_fach_id'] = '';
        $_SESSION['mvv_filter_lvg_abschluss_id'] = '';

        $this->filter = [];
        $this->sessRemove('filter');
        $this->semester_filter = null;
        $this->sessRemove('semester_filter');
        $this->reset_page();
        $this->redirect($this->action_url('index'));
    }

    /**
     * adds the search function to the sidebar
     */
    private function sidebar_search()
    {
        $query = "
            SELECT lvgruppe_id, name
            FROM mvv_lvgruppe
            WHERE name LIKE :input";
        $search_term = $this->search_term ? $this->search_term : _('LV-Gruppe suchen');

        $sidebar = Sidebar::get();
        $widget = new SearchWidget($this->url_for('lvgruppen/lvgruppen/search'));
        $widget->addNeedle(
            _('LV-Gruppe suchen'),
            'lvgruppe_suche',
            true,
            new SQLSearch($query, $search_term, 'lvgruppe_id'),
            'function () { $(this).closest("form").submit(); }',
            $this->search_term
        );
        $sidebar->addWidget($widget, 'search');
    }

    private function set_trails_filter($start, $end)
    {
        // show only pathes with modules valid in the selected semester
        ModuleManagementModelTreeItem::setObjectFilter('Modulteil',
            function ($mt) use ($start, $end) {
                $modul_start = Semester::find($mt->modul->start)->beginn ?: 0;
                $modul_end = Semester::find($mt->modul->end)->ende ?: PHP_INT_MAX;
                return ($modul_start <= $end && $modul_end >= $start);
            }
        );
    }

    /**
     * Returns the ids of the lvgruppen related to the given subject id.
     *
     * @param string $fach_id The id of the selected subject.
     * @return array The ids of the modules related to the subject.
     */
    private function findLvgIdsByFach(string $fach_id): array
    {
        $query = "SELECT `mvv_lvgruppe_modulteil`.`lvgruppe_id`
                  FROM `mvv_lvgruppe_modulteil`
                  JOIN `mvv_modulteil` USING (`modulteil_id`)
                  JOIN `mvv_stgteilabschnitt_modul` USING (`modul_id`)
                  JOIN `mvv_stgteilabschnitt` USING (`abschnitt_id`)
                  JOIN `mvv_stgteilversion` USING (`version_id`)
                  JOIN `mvv_stgteil` USING (`stgteil_id`)
                  WHERE `mvv_stgteil`.`fach_id` = ?";
        return DBManager::get()->fetchFirst($query, [$fach_id]);
    }

    /**
     * Returns the ids of the lvgruppen related to the given degree id.
     *
     * @param string $abschluss_id The id of the selected degree.
     * @return array The ids of the lvgruppen related to the degree.
     */
    private function findLvgIdsByAbschluss(string $abschluss_id): array
    {
        $query = "SELECT `mvv_lvgruppe_modulteil`.`lvgruppe_id`
                  FROM `mvv_lvgruppe_modulteil`
                  JOIN `mvv_modulteil` USING (`modulteil_id`)
                  JOIN `mvv_stgteilabschnitt_modul` USING (`modul_id`)
                  JOIN `mvv_stgteilabschnitt` USING (`abschnitt_id`)
                  JOIN `mvv_stgteilversion` USING (`version_id`)
                  JOIN `mvv_stg_stgteil` USING (`stgteil_id`)
                  JOIN `mvv_studiengang` USING (`studiengang_id`)
                  WHERE `mvv_studiengang`.`abschluss_id` = ?";
        return DBManager::get()->fetchFirst($query, [$abschluss_id]);
    }
}
