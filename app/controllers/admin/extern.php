<?php
/**
 * extern.php - administration controller for system-wide external pages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     extern
 * @since       5.5
 */

class Admin_ExternController extends AuthenticatedController
{

    protected $range;
    protected $template_path;

    /**
     * @see PluginController::before_filter()
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $this->checkPerm();
        $this->init();
        $this->setSidebar();
    }

    /**
     * Initialize the controller.
     */
    protected function init()
    {
        $this->range = 'studip';
        $nav = Navigation::getItem('admin/locations/external');
        if ($nav) {
            $nav->setActive(true);
        }

        $this->getSystemWideConfigTypes();
        $this->config_types['PersBrowse'] = [
            'name'        => _('Personen-Browser'),
            'description' => _('Personal der Einrichtungen'),
            'icon'        => 'person',
            'template'    => 'admin/extern/extern_config/persbrowse'
        ];

        $this->fetchPlugins(true);

        PageLayout::setTitle(_('Externe Seiten (Systemweit)'));
    }

    /**
     * Action to show index page.
     */
    public function index_action()
    {
        $configs = [];
        $count_not_migrated = 0;
        ExternPageConfig::findEachBySQL(
            function ($c) use (&$configs, &$count_not_migrated) {
                $configs[$c->type][] = $c;
                if (isset($c->conf['not_fixed_after_migration'])) { 
                    $count_not_migrated++; 
                }
            },
            "range_id = ?", [$this->range]
        );
        $this->configs = $configs;

        if ($count_not_migrated > 0) {
            PageLayout::postInfo(
                _('Die mit einem Ausrufezeichen versehenen Konfigurationen müssen eventuell überarbeitet werden.'),
                [
                    _('Der Aufbau der Templates hat sich in der aktuellen Stud.IP-Version geändert.'),
                    _('Nach dem Speichern der gekennzeichneten Templates wird die Markierung entfernt.')
                ]
            );
        }
        $actions_widget = Sidebar::get()->getWidget('actions');
        $actions_widget->addLink(
            _('Konfiguration hochladen'),
            $this->uploadURL(),
            Icon::create('upload'),
            ['data-dialog' => 'size=auto']
        );

        $this->render_template('institute/extern/index', $this->layout);
    }

    /**
     * Shows dialog to select a new page configuration
     */
    public function new_action()
    {
        PageLayout::setTitle(_('Neue externe Seite anlegen'));
        $this->render_template('institute/extern/new', $this->layout);
    }

    /**
     * Create a new configuration or edit an existing one.
     *
     * @param string $type_id The type of the configuration.
     * @param string $config_id The id of the configuration.
     * @throws AccessDeniedException
     * @throws InvalidSecurityTokenException
     * @throws MethodNotAllowedException
     */
    public function edit_action(string $type_id, string $config_id = '')
    {
        $this->load($type_id, $config_id);
        $actions_widget = Sidebar::get()->getWidget('actions');
        $actions_widget->addLink(
            _('Vorschau anzeigen'),
            $this->url_for('extern/index', $this->config->id),
            Icon::create('log'),
            ['target' => '_blank']
         );
        $actions_widget->addLink(
            _('Informationen anzeigen'),
            $this->infoURL($this->config->id),
            Icon::create('infopage'),
            ['data-dialog' => 'size=auto']
        );
        $actions_widget->addLink(
            _('Konfiguration herunterladen'),
            $this->downloadURL($this->config->id),
            Icon::create('download')
        );
        $actions_widget->addLink(
            _('Konfiguration hochladen'),
            $this->uploadURL($this->config->id),
            Icon::create('upload'),
            ['data-dialog' => 'size=auto']
        );

        $this->render_template($this->config_types[$type_id]['template'], $this->layout);
    }

    /**
     * Action to store an external page with configuration.
     *
     * @param string $type_id The type of the external page.
     * @param string $config_id The id of the configuration.
     * @throws AccessDeniedException
     * @throws InvalidSecurityTokenException
     * @throws MethodNotAllowedException
     */
    public function store_action(string $type_id, string $config_id = '')
    {
        $this->load($type_id, $config_id);
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $this->page->conf        = Request::extract(($this->page->getConfigFields()));
            $this->page->name        = Request::get('name');
            $this->page->description = Request::get('description');
            $this->page->template    = Request::get('template');
            if ($this->page->store()) {
                if ($this->page->page_config->isNew()) {
                    PageLayout::postSuccess(sprintf(
                        _('Eine neue externe Seite "%$1s" vom Typ %$2s wurde angelegt.'),
                        htmlReady($this->page->name), 
                        htmlReady($this->page->type)
                    ));
                } else {
                    PageLayout::postSuccess(
                        sprintf(_('Die Konfiguration der externen Seite "%s" wurde gespeichert.'),
                        htmlReady($this->page->name)
                    ));
                }
            } else {
                PageLayout::postInfo(_('Es wurden keine Änderungen vorgenommen'));
            }
            if (Request::submitted('store_cancel')) {
                $this->redirect($this->indexURL(['open_type' => $type_id]));
                return;
            }
        }
        $this->redirect($this->editURL($type_id, $this->page->id));
    }

    /**
     * Loads the configuration to edit or store.
     *
     * @param string $type_id The type of the external page.
     * @param string $config_id The id of the external page.
     * @throws AccessDeniedException
     */
    protected function load(string $type_id, string $config_id = '')
    {
        if (!$config_id) {
            if (!in_array($type_id, array_keys($this->config_types))) {
                throw new AccessDeniedException();
            }
            $this->config = new ExternPageConfig();
            $this->config->type = $type_id;
            $this->config->range_id = $this->range;
        } else {
            $this->config = ExternPageConfig::find($config_id);
            if ($this->config->range_id === 'studip') {
                $GLOBALS['perm']->check('root');
                $have_perm = true;
            } else {
                $have_perm = $GLOBALS['perm']->have_studip_perm('admin', $this->config->range_id);
            }
            if (!$this->config || !$have_perm) {
                throw new AccessDeniedException();
            }
        }
        $this->page = ExternPage::get($this->config);
    }

    /**
     * Deletes a configuration.
     *
     * @param string $config_id The id of the configuration to delete.
     * @throws AccessDeniedException
     * @throws InvalidSecurityTokenException
     * @throws MethodNotAllowedException
     */
    public function delete_action(string $config_id)
    {
        $config_type = '';
        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();
            $config = ExternPageConfig::find($config_id);
            if ($config->range_id === 'studip') {
                $GLOBALS['perm']->check('root');
                $have_perm = true;
            } else {
                $have_perm = $GLOBALS['perm']->have_studip_perm('admin', $config->range_id);
            }
            if (!$config || !$have_perm) {
                throw new AccessDeniedException();
            }
            if ($config) {
                $config_name = $config->name;
                $config_type = $config->type;
                if ($config->delete()) {
                    PageLayout::postSuccess(
                        sprintf(_('Die Konfiguration "%s" wurde gelöscht.'), htmlReady($config_name))
                    );
                }
            }
        }
        $this->redirect($this->indexURL(['open_type' => $config_type]));
    }

    /**
     * Show dialog with information about the given configuration.
     *
     * @param $config_id
     */
    public function info_action(string $config_id)
    {
        $this->page = ExternPage::get(ExternPageConfig::find($config_id));
        if ($this->page->author) {
            $this->author = '<a href="'
                . URLHelper::getLink('dispatch.php/profile', ['username' => $this->page->author->username])
                . '">'
                . htmlReady($this->page->author)
                . '</a>';
        } else {
            $this->author = _('unbekannt');
        }
        if ($this->page->editor) {
            $this->editor = '<a href="'
                . URLHelper::getLink('dispatch.php/profile', ['username' => $this->page->editor->username])
                . '">'
                . htmlReady($this->page->editor)
                . '</a>';
        } else {
            $this->editor = _('unbekannt');
        }
        PageLayout::setHelpKeyword('Basis.ExterneSeiten' . $this->page->type);
        $this->datafields = $this->page->getDataFields();
        $this->render_template('institute/extern/info', $this->layout);
    }

    /**
     * Action to download given configuration.
     *
     * @param $config_id
     */
    public function download_action(string $config_id)
    {
        $config = ExternPageConfig::find($config_id);
        $perm = $GLOBALS['perm']->have_studip_perm('admin', $config->range_id)
            || ($GLOBALS['perm']->have_perm('root') && $config->range_id === 'studip');
        if (!$config || !$perm) {
            throw new AccessDeniedException();
        }
        $this->response->add_header(
            'Content-Disposition',
            'attachment; ' . encode_header_parameter('filename', $config->name . '.cfg')
        );
        $this->render_json($config->toArray('type name description conf template'));
    }

    /**
     * Action to create a new configuration from uploaded file.
     */
    public function upload_action()
    {
        $this->render_template('institute/extern/upload', $this->layout);
    }

    /**
     * Action to import a configuration as json file.
     *
     * @return void
     * @throws InvalidSecurityTokenException
     * @throws MethodNotAllowedException
     */
    public function import_action()
    {
        if (Request::submitted('import')) {
            $this->config_name = Request::get('config_name', '');
            CSRFProtection::verifyUnsafeRequest();
            $file = $_FILES['config_file'];
            if (!$file) {
                PageLayout::postError(_('Es wurde keine Datei ausgewählt!'));
                $this->redirect($this->uploadURL());
                return;
            }
            $config_data = json_decode(file_get_contents($file['tmp_name']));
            if (!$config_data) {
                PageLayout::postError(_('Die Datei hat ein ungültiges Format!'));
                $this->redirect($this->uploadURL());
                return;
            }
            $config = new ExternPageConfig();
            $fields = [
                'name',
                'description',
                'conf',
                'template',
                'type',
            ];
            foreach ($fields as $field) {
                if (isset($config_data->$field)) {
                    $config->$field = $config_data->$field;
                } else {
                    PageLayout::postError(_('Die Datei kann nicht importiert werden!'));
                    $this->redirect($this->uploadURL());
                    return;
                }
            }
            if (!in_array($config->type, array_keys($this->config_types))) {
                PageLayout::postError(_('Der Typ der Konfiguration ist ungültig!'));
                $this->redirect($this->uploadURL());
                return;
            }
            if (trim($this->config_name)) {
                $config->name = $this->config_name;
            }
            $config->range_id = $this->range;
            $config->author_id = $config->editor_id = $GLOBALS['user']->id;
            $config->store();
            PageLayout::postSuccess(
                sprintf(_('Die Konfiguration "%s" wurde erfolgreich importiert.'), 
                htmlReady($config->name)
            ));
        }
        $this->relocate($this->indexURL(['open_type' => $config->type]));
    }

    /**
     * Action to get study areas by search term.
     *
     * @param string $search_term The search term.
     * @return void
     */
    public function search_studyareas_action(string $search_term = '')
    {
        $search_term = Request::get('term', $search_term);
        $paths = [];
        $study_areas = StudipStudyArea::search($search_term);
        foreach ((array) $study_areas as $study_area) {
            if ($study_area->isHidden()) {
                continue;
            }
            $path = $study_area->getPath(' > ');
            $paths[$path] = [
                'id'   => $study_area->id,
                'text' => $path
            ];
        }
        ksort($paths, SORT_LOCALE_STRING);

        $this->render_json([
            'results' => array_values($paths)
        ]);
    }

    /**
     * Checks for sufficient rights.
     *
     * @throws AccessDeniedException
     */
    protected function checkPerm()
    {
        if (!$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Creates the sidebar menu.
     */
    protected function setSidebar()
    {
        if ($this->range) {
            $actions_widget = new ActionsWidget();
            $actions_widget->addLink(
                _('Neue externe Seite anlegen'),
                $this->newURL(),
                Icon::create('settings'),
                ['data-dialog' => 'size=870x500']
            );
            Sidebar::Get()->addWidget($actions_widget);
        }
    }

    /**
     * Retrofits navigation with plugins.
     *
     * @param bool $is_system True to fetch system-wide external page plugins.
     * @return void
     */
    protected function fetchPlugins(bool $is_system): void
    {
        $plugins = PluginEngine::getPlugins('ExternPagePlugin');
        foreach ($plugins as $plugin) {
            if (
                $is_system === $plugin->isSystemPage()
                || !$is_system === $plugin->isInstitutePage()
            ) {
                $nav = $plugin->getConfigurationFormNavigation();
                $this->config_types[$nav->name] = [
                    'name' => $nav->title,
                    'description' => $nav->description,
                    'icon' => $nav->image,
                    'template' => $plugin->getConfigurationFormTemplate(),
                ];
            }
        }
    }

    /**
     * Sets data for system-wide external pages.
     *
     * @return void
     */
    protected function getSystemWideConfigTypes(): void
    {
        $this->config_types = [
            'PersonDetails'  => [
                'name'        => _('Personen-Details'),
                'description' => _('Details zu einer Person'),
                'icon'        => 'person2',
                'template'    => 'institute/extern/extern_config/persondetails'
            ],
            'Courses'       => [
                'name'        => _('Veranstaltungen'),
                'description' => _('Liste der Veranstaltungen'),
                'icon'        => 'seminar',
                'template'    => 'institute/extern/extern_config/courses'
            ],
            'CourseDetails' => [
                'name'        => _('Veranst.-Details'),
                'description' => _('Details zu einer Veranstaltung'),
                'icon'        => 'seminar',
                'template'    => 'institute/extern/extern_config/coursedetails'
            ],
            'Timetable'     => [
                'name'        => _('Termine'),
                'description' => _('Zeiten/Termine/Themen der Veranstaltungen'),
                'icon'        => 'date-cycle',
                'template'    => 'institute/extern/extern_config/timetable'
            ]
        ];
    }

}
