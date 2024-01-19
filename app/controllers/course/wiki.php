<?php
/**
 * wiki.php - wiki controller
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>, Rasmus Fuhse <fuhse@data-quest.de>
 * @license GPL2 or any later version
 * @since   3.3
 */

class Course_WikiController extends AuthenticatedController
{
    protected $allow_nobody = true;
    protected $with_session = true;
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        object_set_visit_module('wiki');
        $this->range = Context::get();
    }

    public function page_action($page_id = null)
    {
        if ($page_id === null) {
            $page_id = $this->range->getConfiguration()->WIKI_STARTPAGE_ID;
        }
        Navigation::activateItem('/course/wiki/start');
        PageLayout::setTitle(Navigation::getItem('/course/wiki')->getTitle());

        $this->page = new WikiPage($page_id);

        $sidebar = Sidebar::Get();
        if (!$this->page->isReadable()) {
            throw new AccessDeniedException();
        }

        if (!$this->page->isNew()) {
            // Table of Contents/QuickLinks
            $widget = Sidebar::Get()->addWidget(new ListWidget());
            $widget->setTitle(_('QuickLinks'));
            $quicklinks = WikiPage::findOneBySQL("`name` = 'toc' AND `range_id` = ?", [$this->range->id]);
            $toc_content = $quicklinks ? '<div class="wikitoc" id="00toc">' . wikiReady($quicklinks['content'], true, $this->range->id) . '</div>' : '';
            $toc_content_empty = !trim(strip_tags($toc_content));
            if (
                (!$quicklinks && $GLOBALS['perm']->have_studip_perm($this->range->getConfiguration()->WIKI_CREATE_PERMISSION, $this->range->id))
                || ($quicklinks && $quicklinks->isEditable())
            ) {
                $extra = sprintf(
                    '<a href="%s">%s</a>',
                    URLHelper::getLink('dispatch.php/course/wiki/edit_toc'),
                    $toc_content_empty
                        ? Icon::create('add')->asImg(['title' => _('Erstellen')])
                        : Icon::create('edit')->asImg(['title' => _('Bearbeiten')])
                );
                $widget->setExtra($extra);
            }
            $element = new WidgetElement($toc_content_empty ? _('Keine QuickLinks vorhanden') : $toc_content);
            $element->icon = Icon::create('link-intern');
            $widget->addElement($element);
        }

        $this->edit_perms = $this->range->getConfiguration()->WIKI_CREATE_PERMISSION;
        if (
            $this->edit_perms === 'all'
            || $GLOBALS['perm']->have_studip_perm($this->edit_perms, $this->range->id)
        ) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Neue Wiki-Seite anlegen'),
                $this->new_pageURL($this->page->id),
                Icon::create('add'),
                ['data-dialog' => 'width=700']
            );
            if ($GLOBALS['perm']->have_studip_perm('tutor', $this->range->id)) {
                $actions->addLink(
                    _('Wiki verwalten'),
                    $this->adminURL(),
                    Icon::create('admin'),
                    ['data-dialog' => 'width=700']
                );
            }
            if ($GLOBALS['perm']->have_studip_perm('tutor', $this->range->id)) { //minimum perm tutor necessary
                $actions->addLink(
                    _('Seiten aus Veranstaltung importieren'),
                    $this->importURL(),
                    Icon::create('import'),
                    ['data-dialog' => 'width=700']
                );
            }
            $sidebar->addWidget($actions);
        }

        if (!$this->page->isNew()) {
            //then the wiki is not empty
            $search = new SearchWidget($this->searchURL());
            $search->addNeedle(
                _('Im Wiki suchen'),
                'search',
                true
            );
            $sidebar->addWidget($search);

            $sidebar->addWidget($this->getViewsWidget($this->page, 'read'));

            $exports = new ExportWidget();
            $exports->addLink(
                _('Als PDF exportieren'),
                $this->pdfURL($this->page->id),
                Icon::create('file-pdf')
            );
            $sidebar->addWidget($exports);
        }

        $startPage = WikiPage::find($this->range->getConfiguration()->WIKI_STARTPAGE_ID);
        $this->contentbar = ContentBar::get()
            ->setTOC(CoreWiki::getTOC($this->page->parent && $startPage ? $startPage : $this->page, $this->page['name']))
            ->setIcon(Icon::create('wiki'))
            ->setInfo(sprintf(
                _('Version %1$s, geändert von %2$s <br> am %3$s'),
                $this->page->versionnumber,
                sprintf(
                    '<a href="%s">%s</a>',
                    URLHelper::getLink('dispatch.php/profile', ['username' => get_username($this->page['user_id'])]),
                    htmlReady(get_fullname($this->page['user_id']))
                ),
                date('d.m.Y H:i:s', $this->page['chdate'])
            ));
        $action_menu = ActionMenu::get();
        if ($this->page->isEditable()) {
            $action_menu->addLink(
                $this->editURL($this->page),
                _('Bearbeiten'),
                Icon::create('edit')
            );
            $action_menu->addLink(
                $this->pagesettingsURL($this->page->id),
                _('Seiteneinstellungen'),
                Icon::create('settings'),
                ['data-dialog' => 'width=700']
            );
            $action_menu->addButton(
                'delete',
                _('Seite löschen'),
                Icon::create('trash'),
                ['data-confirm' => _('Wollen Sie wirklich die komplette Seite löschen?'), 'form' => 'delete_page']
            );
        }
        $action_menu->addLink(
            '#',
            _('Als Vollbild anzeigen'),
            Icon::create('screen-full'),
            ['class' => 'fullscreen-trigger hidden-medium-down']
        );
        $this->contentbar->setActionMenu($action_menu);
    }

    public function pagesettings_action(WikiPage $page)
    {
        if (!$page->isEditable()) {
            throw new AccessDeniedException();
        }
        $options = [
            '' => _('Keine')
        ];
        $descendants_ids = array_map(
            function ($d) {
                return $d->id;
            },
            $page->getDescendants()
        );
        WikiPage::findEachBySQL(
            function (WikiPage $p) use ($page, &$options, $descendants_ids) {
                if ($p->id !== $page->id && !in_array($p->id, $descendants_ids)) {
                    $options[$p->id] = $p->name;
                }
            },
            'range_id = ? ORDER BY name',
            [$this->range->id]
        );
        $groups = [
            'all' => _('Alle'),
            'tutor' => _('Lehrende und Tutoren/Tutorinnen'),
            'dozent' => _('Nur Lehrende')
        ];
        Statusgruppen::findEachBySQL(
            function (Statusgruppen $group) use (&$groups) {
                $groups[$group->id] = $group->name;
            },
            '`range_id` = ? ORDER BY `name`',
            [$this->range->id]
        );
        $oldname = $page->name;
        $this->form = \Studip\Forms\Form::fromSORM(
            $page,
            [
                'legend' => _('Seiteneinstellung'),
                'fields' => [
                    'name' => [
                        'label' => _('Titel der Seite'),
                        'required' => true,
                        'validate' => function ($value, $input) {
                            $page = $input->getContextObject();
                            if ($value !== $page->name) {
                                $page2 = WikiPage::findOneBySQL('`range_id` = :range_id AND `name` = :name', [
                                    'range_id' => $page['range_id'],
                                    'name' => $value
                                ]);
                                if ($page2) {
                                    return _('Dieser Name ist bereits vergeben.');
                                }
                            }
                            return true;
                        }
                    ],
                    'parent_id' => [
                        'label' => _('Übergeordnete Seite im Inhaltsverzeichnis'),
                        'type' => 'select',
                        'options' => $options
                    ],
                    'read_permission' => [
                        'label' => _('Lesezugriff für'),
                        'type' => 'select',
                        'options' => $groups
                    ],
                    'write_permission' => [
                        'label' => _('Schreibzugriff für'),
                        'type' => 'select',
                        'options' => $groups
                    ]
                ]
            ],
            $this->pagesettingsURL($page->id)
        )->addStoreCallback(function ($form, $values) use ($oldname) {
            if ($values['name'] === $oldname) {
                return;
            }
            $page = $form->getLastPart()->getContextObject();
            $other_pages = WikiPage::findBySQL(
                "`range_id` = :range_id AND `page_id` != :page_id AND `content` LIKE :search",
                [
                    'page_id' => $page->id,
                    'range_id' => $page['range_id'],
                    'search' => '%' . $oldname . '%',
                ]
            );

            foreach ($other_pages as $p2) {
                $p2['content'] = preg_replace(
                    "/\[\[\s*" . $oldname . "\b/",
                    "[[ " . $values['name'],
                    $p2['content']
                );
                $p2->store();
            }
        })->validate();
        if (Request::isPost()) {
            $this->form->store();
            PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
            if ($page->isReadable()) {
                $this->redirect($this->pageURL($page->id));
            } else {
                $this->redirect($this->allpagesURL());
            }
            return;
        }
        $this->render_form($this->form);
    }

    public function delete_action(WikiPage $page)
    {
        if (!Request::isPost() || !CSRFProtection::verifyRequest()) {
            throw new AccessDeniedException();
        }
        $name = $page->name;
        $page->delete();
        PageLayout::postSuccess(sprintf(_('Die Seite %s wurde gelöscht.'), htmlReady($name)));
        $this->redirect($this->allpagesURL());
    }

    public function allpages_action()
    {
        Navigation::activateItem('/course/wiki/allpages');
        $this->pages = WikiPage::findBySQL(
            "`range_id` = ? ORDER BY `name` ASC",
            [$this->range->id]
        );
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->range->id)) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Neue Wiki-Seite anlegen'),
                $this->new_pageURL(),
                Icon::create('add'),
                ['data-dialog' => 'width=700']
            );
            $actions->addLink(
                _('Wiki verwalten'),
                $this->adminURL(),
                Icon::create('admin'),
                ['data-dialog' => 'width=700']
            );
            Sidebar::Get()->addWidget($actions);
        }

        $search = new SearchWidget($this->searchURL());
        $search->addNeedle(
            _('Im Wiki suchen'),
            'search',
            true
        );
        Sidebar::Get()->addWidget($search);

        $widget = new ExportWidget();
        $widget->addLink(
            _('Alle Wiki-Seiten als PDF exportieren'),
            $this->pdf_allpagesURL(),
            Icon::create('file-pdf')
        );
        Sidebar::Get()->addWidget($widget);
    }

    public function admin_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range->id)) {
            throw new AccessDeniedException();
        }
        $this->config = $this->range->getConfiguration();
        $this->pages = WikiPage::findBySQL(
            '`range_id` = ? ORDER BY `name` ASC',
            [$this->range->id]
        );
    }

    public function store_course_config_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->range->id)) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();
        $this->config = $this->range->getConfiguration();
        $this->config->store('WIKI_STARTPAGE_ID', trim(Request::option('wiki_startpage_id')));
        if (
            $this->config->WIKI_CREATE_PERMISSION === 'all'
            || $GLOBALS['perm']->have_studip_perm($this->config->WIKI_CREATE_PERMISSION, Context::getId())
        ) {
            $this->config->store('WIKI_CREATE_PERMISSION', trim(Request::option('wiki_create_permission')));
        }
        if (
            $this->config->WIKI_RENAME_PERMISSION === 'all'
            || $GLOBALS['perm']->have_studip_perm($this->config->WIKI_RENAME_PERMISSION, $this->range->id)
        ) {
            $this->config->store('WIKI_RENAME_PERMISSION', trim(Request::option('wiki_rename_permission')));
        }
        PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        if (WikiPage::countBySQL('`range_id` = ? ORDER BY `name` ASC', [$this->range->id]) > 0) {
            $this->redirect($this->allpagesURL());
        } else {
            $this->redirect($this->pageURL());
        }
    }

    public function edit_action(WikiPage $page = null)
    {
        if ($page->isNew() && Request::get('keyword')) {
            $name = trim(Request::get('keyword'));
            $page = WikiPage::findOneBySQL('`name` = :name AND `range_id` = :range_id', [
                'name' => $name,
                'range_id' => Context::getId()
            ]);
            if (!$page) {
                $page = WikiPage::create([
                    'name'      => $name,
                    'range_id'  => Context::getId(),
                    'parent_id' => Request::option('parent_id', $this->range->getConfiguration()->WIKI_STARTPAGE_ID),
                ]);
            }
            $this->redirect($this->editURL($page));
            return;
        }
        if (!$page->isEditable()) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem('/course/wiki/start');
        $user = User::findCurrent();
        WikiOnlineEditingUser::deleteBySQL(
            "`page_id` = :page_id AND `chdate` < UNIX_TIMESTAMP() - :threshold",
            [
                'page_id' => $page->id,
                'threshold' => WikiOnlineEditingUser::$threshold
            ]
        );
        $pageData = [
            'page_id' => $page->id,
            'user_id' => $user->id
        ];
        $online_user = WikiOnlineEditingUser::findOneBySQL(
            '`page_id` = :page_id AND `user_id` = :user_id',
            $pageData
        );
        if (!$online_user) {
            $online_user = WikiOnlineEditingUser::build($pageData);
        }
        $editingUsers = WikiOnlineEditingUser::countBySQL(
            "`page_id` = ? AND `editing` = 1 AND `user_id` != ?",
            [$page->id, $user->id]
        );
        $online_user->editing = $editingUsers === 0 ? 1 : 0;
        $online_user->chdate = time();
        $online_user->store();
        $this->me_online = $online_user;
        $this->online_users = WikiOnlineEditingUser::findBySQL(
            "JOIN `auth_user_md5` USING (`user_id`)
             WHERE `page_id` = ?
             ORDER BY Nachname, Vorname",
            [$page->id]
        );
        $startPage = WikiPage::find($this->range->getConfiguration()->WIKI_STARTPAGE_ID);
        $this->contentbar = ContentBar::get()
            ->setTOC(CoreWiki::getTOC($startPage, $page['name']))
            ->setIcon(Icon::create('wiki'))
            ->setInfo(_('Zuletzt gespeichert') .': '. '<studip-date-time :timestamp="Math.floor(lastSaveDate / 1000)" :relative="true"></studip-date-time>');
    }

    public function apply_editing_action(WikiPage $page)
    {
        if (!$page->isEditable() || !Request::isPost()) {
            throw new AccessDeniedException();
        }
        $user = User::findCurrent();
        $pageData = [
            'page_id' => $page->id,
            'user_id' => $user->id
        ];
        $online_user = WikiOnlineEditingUser::findOneBySQL(
            '`page_id` = :page_id AND `user_id` = :user_id',
            $pageData
        );
        if (!$online_user) {
            $online_user = WikiOnlineEditingUser::build($pageData);
        }
        $editingUsers = WikiOnlineEditingUser::countBySQL(
            "`page_id` = ? AND `editing` = 1 AND `user_id` != ?",
            [$page->id, $user->id]
        );
        if ($editingUsers > 0) {
            $online_user->editing_request = 1;
        } else {
            $online_user->editing = 1;
        }
        $online_user->store();
        $output = [
            'me_online' => $online_user->toArray(),
            'users' => $page->getOnlineUsers()
        ];
        $this->render_json($output);
    }

    public function leave_editing_action(WikiPage $page)
    {
        if (!$page->isEditable()) {
            throw new AccessDeniedException();
        }
        $user = User::findCurrent();
        $pageData = [
            'page_id' => $page->id,
            'user_id' => $user->id
        ];
        WikiOnlineEditingUser::deleteBySQL(
            '`page_id` = :page_id AND `user_id` = :user_id',
            $pageData
        );
        $this->redirect($this->pageURL($page));
    }

    public function delegate_edit_mode_action(WikiPage $page, $user_id)
    {
        if (!$page->isEditable() || !Request::isPost()) {
            throw new AccessDeniedException();
        }
        $user = User::findCurrent();
        $pageData = [
            'page_id' => $page->id,
            'user_id' => $user->id
        ];
        $online_user_me = WikiOnlineEditingUser::findOneBySQL(
            '`page_id` = :page_id AND `user_id` = :user_id',
            $pageData
        );
        if (!$online_user_me->editing) {
            $this->render_json([
                'error' => 'not_in_edit_mode'
            ]);
        }
        $online_user_them = WikiOnlineEditingUser::findOneBySQL(
            '`page_id` = :page_id AND `user_id` = :user_id',
            ['page_id' => $page->id, 'user_id' => $user_id]
        );
        if (!$online_user_them || !$online_user_them->editing_request) {
            $this->render_json([
                'error' => 'user_not_requested_edit_mode'
            ]);
        }

        $online_user_me->editing = 0;
        $online_user_me->store();

        $online_user_them->editing_request = 1; //that will be set to 0 by the user themself
        $online_user_them->editing = 1;
        $online_user_them->store();

        $this->render_json([
            'message' => 'edit mode delegated'
        ]);
    }

    public function save_action(WikiPage $page)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (!$page->isEditable()) {
            throw new AccessDeniedException();
        }

        $page->content = \Studip\Markup::markAsHtml(trim(Request::get('content')));
        $page->store();
        PageLayout::postSuccess(_('Die Seite wurde gespeichert.'));
        $this->redirect($this->pageURL($page));
    }

    public function edit_toc_action()
    {
        $quicklinks = WikiPage::findOneBySQL(
            "`name` = 'toc' AND `range_id` = ?",
            [$this->range->id]
        );
        if (!$quicklinks) {
            $quicklinks = WikiPage::create([
                'range_id' => $this->range->id,
                'name' => 'toc'
            ]);
        }
        $this->redirect($this->editURL($quicklinks));
    }

    public function newpages_action()
    {
        Navigation::activateItem('/course/wiki/listnew');

        $this->limit = Config::get()->ENTRIES_PER_PAGE;
        $statement = DBManager::get()->prepare("
            SELECT COUNT(*) FROM (
                SELECT `wiki_pages`.`page_id` AS `id`,
                       0 AS `is_version`,
                       `wiki_pages`.`chdate` AS `timestamp`
                FROM `wiki_pages`
                WHERE `wiki_pages`.`range_id` = :range_id

                UNION

                SELECT `wiki_versions`.`version_id` AS `id`,
                       1 AS `is_version`,
                       `wiki_versions`.`mkdate` AS `timestamp`
                FROM `wiki_versions`
                JOIN `wiki_pages` USING (`page_id`)
                WHERE `wiki_pages`.`range_id` = :range_id
            ) AS `all_entries`
        ");
        $statement->execute([
            'range_id' => $this->range->id
        ]);
        $this->num_entries = $statement->fetch(PDO::FETCH_COLUMN);
        $this->page = Request::int('page', 0);

        $statement = DBManager::get()->prepare("
            SELECT `wiki_pages`.`page_id` AS `id`,
                   0 AS `is_version`,
                   `wiki_pages`.`chdate` AS `timestamp`
            FROM `wiki_pages`
            WHERE `wiki_pages`.`range_id` = :range_id

            UNION

            SELECT `wiki_versions`.`version_id` AS `id`,
                   1 AS `is_version`,
                   `wiki_versions`.`mkdate` AS `timestamp`
            FROM `wiki_versions`
            JOIN `wiki_pages` USING (`page_id`)
            WHERE `wiki_pages`.`range_id` = :range_id
            ORDER BY `timestamp` DESC
            LIMIT :offset, :limit
        ");
        $statement->execute([
            'range_id' => $this->range->id,
            'offset' => Request::int('page', 0) * $this->limit,
            'limit' => $this->limit
        ]);
        $this->versions = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['is_version']) {
                $this->versions[] = WikiVersion::find($row['id']);
            } else {
                $this->versions[] = WikiPage::find($row['id']);
            }
        }
    }

    public function history_action(WikiPage $page)
    {
        Navigation::activateItem('/course/wiki/start');
        Sidebar::Get()->addWidget($this->getViewsWidget($this->page, 'history'));
    }

    public function version_action(WikiVersion $version)
    {
        Navigation::activateItem('/course/wiki/start');
        Sidebar::Get()->addWidget($this->getViewsWidget($version->page, 'history'));
        $startPage = WikiPage::find($this->range->getConfiguration()->WIKI_STARTPAGE_ID);
        $this->contentbar = ContentBar::get()
            ->setTOC(CoreWiki::getTOC($startPage, $version->page['name']))
            ->setIcon(Icon::create('wiki'))
            ->setInfo(sprintf(
                _('Version %1$s vom %2$s'),
                $version->versionnumber,
                date('d.m.Y H:i:s', $version['mkdate'])
            ));
    }

    public function blame_action(WikiPage $page)
    {
        Navigation::activateItem('/course/wiki/start');
        Sidebar::Get()->addWidget($this->getViewsWidget($page, 'blame'));

        $version = WikiVersion::findOneBySQL(
            "`page_id` = ? ORDER BY `mkdate` LIMIT 1",
            [$page->id]
        );
        if (!$version) {
            $version = $page;
        }
        $lines = Studip\Markup::removeHtml($version->content);
        $lines = explode("\n", str_replace("\r", '', $lines));
        $this->line_versions = array_fill(0, count($lines), $version);

        $this->diffarray = WikiDiffer::toDiffLineArray($version->content, $version->user_id);
        $differ = new WikiDiffer();
        $k = 0;
        while ($version && !is_a($version, WikiPage::class)) {
            $version = $version->successor;
            if ($version) {
                $diffarray2 = WikiDiffer::toDiffLineArray($version->content, $version->user_id);
                $newarray = $differ->arr_compare('diff', $this->diffarray, $diffarray2);
                $this->diffarray = []; //completely rewrite $this->diffarray with newer version
                foreach ($newarray as $number => $i) {
                    if ($i->status['diff'] !== '-') {
                        $this->diffarray[] = $i;
                    }
                    if ($i->status['diff'] === '+') {
                        $this->line_versions[$number] = $version;
                    }
                }
            }
            $k++;
            if ($k > 100) {
                break;
            }
        }
        $this->diffarray[] = null;
    }

    public function diff_action(WikiPage $page)
    {
        Navigation::activateItem('/course/wiki/start');
        Sidebar::Get()->addWidget($this->getViewsWidget($page, 'diff'));

        $this->diffs = [];
        $last_version = null;
        foreach (array_reverse($page->versions->getArrayCopy()) as $version) {
            if ($last_version === null) {
                $last_version = $version;
                $this->diffs[] = [
                    'diff' => WikiDiffer::doDiff(
                        Studip\Markup::removeHtml($version->content),
                        ''
                    ),
                    'version' => $version
                ];
                continue;
            }

            $this->diffs[] = [
                'diff' => WikiDiffer::doDiff(
                    Studip\Markup::removeHtml($version->content),
                    Studip\Markup::removeHtml($last_version->content)
                ),
                'version' => $version
            ];

            $last_version = $version;
        }
        $this->diffs[] = [
            'diff' => WikiDiffer::doDiff(
                Studip\Markup::removeHtml($page->content),
                $last_version !== null ? Studip\Markup::removeHtml($last_version->content) : ''
            ),
            'version' => $page
        ];
    }

    public function versiondiff_action (WikiPage $page, $version_id = null)
    {
        if ($version_id !== null) {
            $this->version = WikiVersion::find($version_id);
        }
        if (
            ($this->version && $this->version->page_id != $page->id)
            || !$page->isReadable()
        ) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Änderungen dieser Version'));
        $content = $this->version ? $this->version->content : $page->content;
        $predecessor = $this->version ? $this->version->predecessor : $page->predecessor;

        $this->diff = WikiDiffer::doDiff(
            Studip\Markup::removeHtml($content),
            Studip\Markup::removeHtml($predecessor ? $predecessor->content : '')
        );
        if (!$this->version) {
            $this->version = $page;
        }
    }

    public function new_page_action($parent_id = null)
    {
        if (
            $this->range->getConfiguration()->WIKI_CREATE_PERMISSION !== 'all'
            && !$GLOBALS['perm']->have_studip_perm($this->range->getConfiguration()->WIKI_CREATE_PERMISSION, $this->range->id)
        ) {
            throw new AccessDeniedException();
        }
        $page = new WikiPage();
        $page->parent_id = $parent_id ?? $this->range->getConfiguration()->WIKI_STARTPAGE_ID;
        $parent_id = $parent_id ?? $this->range->getConfiguration()->WIKI_STARTPAGE_ID;
        PageLayout::setTitle(_('Neue Wikiseite erstellen'));
        $options = [
            '-' => _('Keine')
        ];
        WikiPage::findEachBySQL(
            function (WikiPage $p) use (&$options) {
                $options[$p->id] = $p->name;
            },
            'range_id = ? ORDER BY name',
            [$this->range->id]
        );
        $this->form = \Studip\Forms\Form::fromSORM(
            $page,
            [
                'legend' => _('Daten'),
                'fields' => [
                    'range_id' => [
                        'type' => 'no',
                        'mapper' => function () { return $this->range->id; }
                    ],
                    'name' => [
                        'required' => true,
                        'label' => _('Name der Seite'),
                        'validate' => function ($value, $input) {
                            $name_count = WikiPage::countBySql('`name` = :name AND `range_id` = :range_id', [
                                'name' => $value,
                                'range_id' => $this->range->id
                            ]);
                            if ($name_count === 0) {
                                return true;
                            } else {
                                return _('Name existiert schon.');
                            }
                        }
                    ],
                    'parent_id' => [
                        'label' => _('Übergeordnete Seite im Inhaltsverzeichnis'),
                        'type' => 'select',
                        'options' => $options
                    ],
                    'autocreate_links' => [
                        'label' => _('Den Seitennamen der neuen Seite automatisch in anderen Wikiseiten verlinken.'),
                        'type' => 'checkbox',
                        'permission' => WikiPage::countBySql("`range_id` = ?", [$this->range->id]) > 0
                    ]
                ]
            ],
            $this->allpagesURL()
        )->addStoreCallback(function ($form, $values) {
                $page = $form->getLastPart()->getContextObject();
                $other_pages = WikiPage::countBySQL(
                    "`range_id` = :range_id AND `page_id` != :page_id",
                    [
                        'page_id' => $page->id,
                        'range_id' => $page->range_id,
                    ]
                );
                if ($other_pages == 0) {
                    $this->range->getConfiguration()->store('WIKI_STARTPAGE_ID', $page->id);
                }
                if (Request::bool('autocreate_links')) {
                    $pages = WikiPage::findBySQL(
                        "`range_id` = :range_id AND `content` LIKE :search",
                        [
                            'range_id' => $this->range->id,
                            'search' => '%' . $values['name'] . '%',
                        ]
                    );
                    foreach ($pages as $page) {
                        $page->content = preg_replace(
                            "/\b" . $values['name'] . "\b/",
                            '[[ ' . $values['name'] . ' ]]',
                            $page->content
                        );
                        $page->store();
                    }
                }
            }
        )->setURL($this->new_pageURL($parent_id))
         ->validate();
        if (Request::isPost()) {
            $this->form->store();
            $this->redirect($this->editURL($page));
        } else {
            $this->render_form($this->form);
        }
    }

    public function search_action()
    {
        Navigation::activateItem('/course/wiki/allpages');
        if (Request::get('search')) {
            $statement = DBManager::get()->prepare("
                SELECT `wiki_pages`.`page_id`,
                       `wiki_pages`.`name` LIKE :searchterm AS `is_in_name`,
                       `wiki_pages`.`content` LIKE :searchterm AS `is_in_content`,
                       `wiki_versions`.`content` LIKE :searchterm AS `is_in_history`,
                       `wiki_versions`.`name` LIKE :searchterm AS `is_in_old_name`,
                       `wiki_versions`.`version_id`
                FROM `wiki_pages`
                    LEFT JOIN `statusgruppe_user` ON (`statusgruppe_user`.`statusgruppe_id` = `wiki_pages`.`read_permission`)
                    LEFT JOIN `wiki_versions` ON (`wiki_versions`.`page_id` = `wiki_pages`.`page_id` AND (`wiki_versions`.`content` LIKE :searchterm OR `wiki_versions`.`name` LIKE :searchterm))
                WHERE `wiki_pages`.`range_id` = :range_id
                    AND (`wiki_pages`.`name` LIKE :searchterm
                             OR `wiki_pages`.`content` LIKE :searchterm
                             OR `wiki_versions`.`content` LIKE :searchterm
                             OR `wiki_versions`.`name` LIKE :searchterm
                        )
                    AND (
                        `wiki_pages`.`read_permission` = 'all'
                        OR `statusgruppe_user`.`user_id` = :user_id
                        OR `wiki_pages`.`read_permission` = :perm
                        OR (`wiki_pages`.`read_permission` = 'tutor' AND :perm = 'dozent')
                    )
                ORDER BY `is_in_name` DESC, `is_in_content` DESC, `is_in_old_name` DESC, `is_in_history` DESC
            ");
            $search = str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], Request::get('search'));
            $perm = $GLOBALS['perm']->get_perm();
            if (in_array($perm, ['admin', 'root'])) {
                $perm = 'dozent';
            }
            $statement->execute([
                'range_id' => $this->range->id,
                'searchterm' => '%' . $search . '%',
                'perm' => $perm,
                'user_id' => User::findCurrent()->id
            ]);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            $this->pages = [];
            foreach ($data as $row) {
                if (!isset($this->pages[$row['page_id']])) {
                    $this->pages[$row['page_id']] = [
                        'page' => WikiPage::find($row['page_id']),
                        'is_in_name' => $row['is_in_name'],
                        'is_in_content' => $row['is_in_content'],
                        'is_in_history' => $row['is_in_history'],
                        'is_in_old_name' => $row['is_in_old_name'],
                        'versions' => [$row['version_id']]
                    ];
                } else {
                    $this->pages[$row['page_id']]['versions'][] = $row['version_id'];
                    $this->pages[$row['page_id']]['is_in_name'] = max($this->pages[$row['page_id']]['is_in_name'], $row['is_in_name']);
                    $this->pages[$row['page_id']]['is_in_content'] = max($this->pages[$row['page_id']]['is_in_content'], $row['is_in_content']);
                    $this->pages[$row['page_id']]['is_in_history'] = max($this->pages[$row['page_id']]['is_in_history'], $row['is_in_history']);
                    $this->pages[$row['page_id']]['is_in_old_name'] = max($this->pages[$row['page_id']]['is_in_old_name'], $row['is_in_old_name']);
                }
            }
        } else {
            $this->redirect($this->pageURL());
        }

        $search = new SearchWidget($this->searchURL());
        $search->addNeedle(
            _('Im Wiki suchen'),
            'search',
            true
        );
        Sidebar::Get()->addWidget($search);
    }

    public function pdf_action(WikiPage $page)
    {
        if (!$page->isReadable()) {
            throw new AccessDeniedException();
        }
        $document = new ExportPDF();
        $document->SetTitle(_('Wiki: ') . $page->name);
        $document->setHeaderTitle(sprintf(_('Wiki von "%s"'), $this->range->name));
        $document->setHeaderSubtitle(sprintf(_('Seite: %s'), $page->name));
        $document->addPage();
        $content = $page->content;
        //remove wiki-links:
        $content = preg_replace('/\[\[[^|\]]*\|([^]]*)\]\]/', '$1', $content);
        $content = preg_replace('/\[\[([^|\]]*)\]\]/', '$1', $content);
        $document->writeHTML($content);
        $this->render_pdf(
            $document,
            Context::getHeaderLine() . ' - ' . $page->name . '.pdf',
            true
        );
    }

    public function pdf_allpages_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm('user', Context::getId())) {
            throw new AccessDeniedException();
        }
        $pages = WikiPage::findBySql('`range_id` = ? ORDER BY `name` ASC', [Context::getId()]);

        $document = new ExportPDF();
        $document->SetTitle(_('Wiki: ') . Context::get()->name);
        $document->setHeaderTitle(sprintf(_('Wiki von "%s"'), Context::get()->name));

        foreach ($pages as $page) {
            if (!$page->isReadable()) {
                continue;
            }

            $document->setHeaderSubtitle(sprintf(_('Seite: %s'), $page->name));
            $document->addPage();

            // We need the @ in front since TCPDF might throw warning that can lead
            // to errors viewing the document
            $content = $page->content;
            //remove wiki-links:
            $content = preg_replace('/\[\[[^|\]]*\|([^]]*)\]\]/', '$1', $content);
            $content = preg_replace('/\[\[([^|\]]*)\]\]/', '$1', $content);
            //@$document->addContent($content);
            @$document->writeHTML($content);
        }
        $this->render_pdf(
            $document,
            Context::getHeaderLine() . '.pdf',
            true
        );
    }

    protected function getViewsWidget(WikiPage $page, string $action): ViewsWidget
    {
        $views = new ViewsWidget();
        $link = $views->addLink(
            _('Lesen'),
            $this->pageURL($page)
        )->setActive($action === 'read');
        $link = $views->addLink(
            _('Seiten-Historie'),
            $this->historyURL($page)
        )->setActive($action === 'history');
        $link = $views->addLink(
            _('Änderungsliste'),
            $this->diffURL($page)
        )->setActive($action === 'diff');
        $link = $views->addLink(
            _('Text mit Autor/-innenzuordnung'),
            $this->blameURL($page)
        )->setActive($action === 'blame');
        return $views;
    }


    /**
     * This action is responsible for importing wiki pages into the wiki
     * of a course from another course.
     */
    public function import_action()
    {
        $edit_perms = $this->range->getConfiguration()->WIKI_CREATE_PERMISSION;
        if ($edit_perms !== 'dozent') {
            $edit_perms = 'tutor';
        }
        if (!$GLOBALS['perm']->have_studip_perm($edit_perms, $this->range->id)) {
            throw new AccessDeniedException(_('Sie haben keine Berechtigung, Änderungen an Wiki-Seiten vorzunehmen!'));
        }

        if (!$this->range) {
            PageLayout::postError(
                _('Die ausgewählte Veranstaltung wurde nicht gefunden!')
            );
        }

        $this->course_search = new QuickSearch(
            'selected_range_id',
            new MyCoursesSearch(
                'Seminar_id',
                $GLOBALS['perm']->get_perm(),
                [
                    'userid'    => User::findCurrent()->id,
                    'exclude'   => [$this->range->id],
                    'institutes' => array_column(Institute::getMyInstitutes(), 'Institut_id')
                ],
                's.`Seminar_id` IN (
                    SELECT range_id FROM wiki_pages
                    WHERE range_id = s.`Seminar_id`
                )'
            )
        );

        $this->course_search->fireJSFunctionOnSelect(
            "function() {jQuery(this).closest('form').submit();}"
        );

        $this->show_wiki_page_form = false;
        $this->bad_course_search = false;
        $this->success = false;

        //The following steps are identical for the search and the import.
        if (Request::submittedSome('selected_range_id', 'import')) {
            CSRFProtection::verifyUnsafeRequest();

            //Search for wiki pages in the selected course:
            $this->selected_range_id = Request::option('selected_range_id');
            $this->selected_course = Course::find($this->selected_range_id);

            if (!$this->selected_course) {
                $this->bad_course_search = true;
                return;
            }

            $this->wiki_pages = WikiPage::findBySQL(
                "`range_id` = ? ORDER BY `name`",
                [$this->selected_course->id]
            );
            $this->show_wiki_page_form = true;
        }

        //The import required additional functionality:
        if (Request::submitted('import')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->selected_wiki_page_ids = Request::getArray('selected_wiki_page_ids');
            if (!$this->selected_wiki_page_ids) {
                PageLayout::postInfo(_('Es wurden keine Wiki-Seiten ausgewählt!'));
                return;
            }

            $selected_wiki_pages = [];
            foreach ($this->selected_wiki_page_ids as $id) {
                $wiki_page = WikiPage::find($id);
                if ($wiki_page) {
                    $selected_wiki_pages[] = $wiki_page;
                }
            }

            if (!$selected_wiki_pages) {
                PageLayout::postError(_('Es wurden keine Wiki-Seiten gefunden!'));
                return;
            }

            $errors = [];
            foreach ($selected_wiki_pages as $selected_page) {
                if ($selected_page->isReadable()) {
                    $count = WikiPage::countBySql(
                        "`range_id` = :range_id AND `name` = :name",
                        [
                            'range_id' => $this->range->id,
                            'name'      => $selected_page['name']
                        ]
                    );
                    if ($count === 0) {
                        $new_page = WikiPage::build([
                            'range_id' => $this->range->id,
                            'user_id'  => $selected_page->user_id,
                            'name'     => $selected_page->name,
                            'content'  => $selected_page->content,
                            'chdate'   => $selected_page->chdate,
                        ]);
                        if (!$new_page->store()) {
                            $errors[] = sprintf(
                                _('Fehler beim Import der Wiki-Seite %s!'),
                                htmlReady($new_page->name)
                            );
                        }
                    }
                }
            }
            if ($errors) {
                PageLayout::postError(
                    _('Die folgenden Fehler traten beim Import auf:'),
                    $errors
                );
            } else {
                $this->show_wiki_page_form = false;
                $this->success = true;
                PageLayout::postSuccess(
                    ngettext(
                        'Die Wiki-Seite wurde importiert! Sie ist unter der Ansicht "Alle Seiten" erreichbar.',
                        'Die Wiki-Seiten wurden importiert! Sie sind unter der Ansicht "Alle Seiten" erreichbar.',
                        count($selected_wiki_pages)
                    )
                );
            }
        }
    }
}
