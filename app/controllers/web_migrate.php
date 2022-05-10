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

        $this->target   = Request::int('target');
        $this->branch   = Request::get('branch', '0');
        $this->version  = new DBSchemaVersion('studip', $this->branch);
        $this->migrator = new Migrator(
            "{$GLOBALS['STUDIP_BASE_PATH']}/db/migrations",
            $this->version,
            true
        );

        $this->setupSidebar($action);

        PageLayout::setTitle(_('Stud.IP Web-Migrator'));
    }

    public function index_action()
    {
        $this->migrations = $this->migrator->relevantMigrations($this->target);
    }

    public function migrate_action()
    {
        $lock = new FileLock('web-migrate');
        $lock_data = ['timestamp' => time(), 'user_id' => $GLOBALS['user']->id];

        if ($lock->tryLock($lock_data)) {
            ob_start();
            set_time_limit(0);

            $this->migrator->migrateTo($this->target);

            $lock->release();

            $announcements = ob_get_clean();
            PageLayout::postSuccess(
                _('Die Datenbank wurde erfolgreich migriert.'),
                array_filter(explode("\n", $announcements))
            );

            $_SESSION['migration-check'] = [
                'timestamp' => time(),
                'count'     => 0,
            ];
        } else {
            $user = User::find($lock_data['user_id']);
            PageLayout::postError(sprintf(
                _('Die Migration wurde %s von %s bereits angestossen und läuft noch.'),
                reltime($lock_data['timestamp']),
                htmlReady($user ? $user->getFullName() : _('unbekannt'))
            ));
        }

        $this->redirect('index');
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
