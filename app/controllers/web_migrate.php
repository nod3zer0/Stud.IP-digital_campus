<?php
class WebMigrateController extends StudipController
{
    public function __construct($dispatcher)
    {
        if (basename($dispatcher->trails_uri, '.php') !== 'web_migrate') {
            throw new Exception('Web Migrator cannot be invoked via standard dispatcher.');
        }

        parent::__construct($dispatcher);
    }

    public function before_filter(&$action, &$args)
    {
        $GLOBALS['auth']->login_if(!$GLOBALS['perm']->have_perm('root'));
        $GLOBALS['perm']->check('root');

        parent::before_filter($action, $args);

        DBSchemaVersion::validateSchemaVersion();

        $this->target   = Request::int('target');
        $this->branch   = Request::get('branch', '0');
        $this->version  = new DBSchemaVersion('studip', $this->branch);
        $this->migrator = new Migrator(
            "{$GLOBALS['STUDIP_BASE_PATH']}/db/migrations",
            $this->version,
            true
        );

        FileLock::setDirectory($GLOBALS['TMP_PATH']);
        $this->lock = new FileLock('web-migrate');

        $this->setupSidebar($action);

        PageLayout::setTitle(_('Stud.IP Web-Migrator'));
    }

    public function index_action()
    {
        $this->migrations = $this->migrator->relevantMigrations($this->target);
    }

    public function migrate_action()
    {
        ob_start();
        set_time_limit(0);

        $this->lock->lock(['timestamp' => time(), 'user_id' => $GLOBALS['user']->id]);

        $this->migrator->migrateTo($this->target);

        $this->lock->release();

        $announcements = ob_get_clean();
        PageLayout::postSuccess(
            _('Die Datenbank wurde erfolgreich migriert.'),
            array_filter(explode("\n", $announcements))
        );

        $_SESSION['migration-check'] = [
            'timestamp' => time(),
            'count'     => 0,
        ];

        $this->redirect('index');
    }

    public function release_action($target)
    {
        if ($this->lock->isLocked()) {
            $this->lock->release();

            PageLayout::postSuccess(_('Die Sperre wurde aufgehoben.'));
        }

        $this->redirect($this->url_for('index', compact('target')));
    }

    public function history_action()
    {
        $this->migrations = $this->migrator->relevantMigrations(0);
        $this->offset = -1;
        $this->target = 0;
        $this->render_action('index');
    }

    public function setupSidebar($action)
    {
        $views = Sidebar::get()->addWidget(new ViewsWidget());
        $views->addLink(
            _('Migrationen ausführen'),
            $this->url_for('index', ['branch' => $this->branch])
        )->setActive($action === 'index');
        $views->addLink(
            _('Migrationen zurücknehmen'),
            $this->url_for('history', ['branch' => $this->branch])
        )->setActive($action === 'history');

        $widget = new SelectWidget(_('Branch'), $this->url_for($action), 'branch');
        Sidebar::get()->addWidget($widget);

        foreach ($this->version->getAllBranches() as $branch) {
            $element = new SelectElement($branch, $branch ?: 'default', $branch == $this->branch);
            $widget->addElement($element);
        }

        $widget = Sidebar::get()->addWidget(new SidebarWidget());
        $widget->setTitle(_('Aktueller Versionsstand'));
        $widget->addElement(new WidgetElement($this->version->get($this->branch)));
    }
}
