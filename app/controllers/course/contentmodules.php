<?php

class Course_ContentmodulesController extends AuthenticatedController
{
    public function index_action()
    {
        Navigation::activateItem('/course/admin/contentmodules');
        PageLayout::setTitle(_('Werkzeuge'));

        if (Context::isCourse()) {
            $this->sem = Context::get();
            $this->sem_class = $this->sem->getSemClass();
        } else {
            $this->sem = Context::get();
            $this->sem_class = SemClass::getDefaultInstituteClass($this->sem['type']);
        }
        $this->modules = $this->getModules($this->sem);

        $this->highlighted_modules = [];
        foreach ($this->modules as $module) {
            if ($module['highlighted']) {
                $this->highlighted_modules[] = $module['id'];
            }
        }

        if (Context::isCourse()) {
            $actions = new ActionsWidget();

            $actions->addLink(
                _('Studierendenansicht simulieren'),
                URLHelper::getURL('dispatch.php/course/change_view/set_changed_view'),
                Icon::create('visibility-invisible')
            );
            Sidebar::Get()->addWidget($actions);
        }

        $views = Sidebar::Get()->addWidget(new ViewsWidget());
        $views->id = 'tool-view-switch';
        $views->addLink(
            _('Kachelansicht'),
            '#tiles'
        )->setActive($GLOBALS['user']->cfg->CONTENTMODULES_TILED_DISPLAY);
        $views->addLink(
            _('Tabellarische Ansicht'),
            '#tabular'
        )->setActive(!$GLOBALS['user']->cfg->CONTENTMODULES_TILED_DISPLAY);

        $this->categories = [];
        foreach ($this->modules as $i => $module) {
            if ($module['category'] && !in_array($module['category'], $this->categories)) {
                $this->categories[] = $module['category'];
            }
            if (!$module['category']) {
                if (!in_array(_('Sonstige'), $this->categories)) {
                    $this->categories[] = _('Sonstige');
                }
                $this->modules[$i]['category'] = _('Sonstige');
            }
        }
        sort($this->categories);

        $filter_widget = Sidebar::Get()->addWidget(new OptionsWidget());
        $filter_widget->id = 'tool-filter-category';
        $filter_widget->setTitle(_('Filter nach Kategorie'));
        $filter_widget->addRadioButton(
            _('Alle Kategorien'),
            '#',
            true
        );
        foreach ($this->categories as $category) {
            $filter_widget->addRadioButton(
                $category,
                '#'
            );
        }

        if (
            Context::isCourse()
            && $GLOBALS['perm']->have_studip_perm('admin', Context::getId())
            && !$this->sem_class['studygroup_mode']
        ) {
            $widget = new CourseManagementSelectWidget();
            Sidebar::Get()->addWidget($widget);
        }

        PageLayout::addHeadElement('script', [
            'type' => 'text/javascript',
        ], sprintf(
            'window.ContentModulesStoreData = %s;',
            json_encode([
                'setCategories' => $this->categories,
                'setHighlighted' => $this->highlighted_modules,
                'setModules' => array_values($this->modules),
                'setUserId' => User::findCurrent()->id,
                'setView' => $GLOBALS['user']->cfg->CONTENTMODULES_TILED_DISPLAY ? 'tiles' : 'table',
            ])
        ));
    }

    public function trigger_action()
    {
        $context = Context::get();

        $required_perm = $context->getRangeType() === 'course' ? 'tutor' : 'admin';
        if (!$GLOBALS['perm']->have_studip_perm($required_perm, $context->id)) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            if ($context->getRangeType() === 'course') {
                $sem_class = $context->getSemClass();
            } else {
                $sem_class = SemClass::getDefaultInstituteClass($context->type);
            }
            $moduleclass = Request::get('moduleclass');
            $active = Request::bool('active', false);
            $module = new $moduleclass;
            if ($module->isActivatableForContext($context)) {
                PluginManager::getInstance()->setPluginActivated($module->getPluginId(), $context->getId(), $active);
            }
            if ($active) {
                $active_tool = ToolActivation::find([$context->id, $module->getPluginId()]);
                $default_position = array_search(get_class($module), $sem_class->getActivatedModules());
                if ($default_position !== false && $active_tool) {
                    $active_tool->position = $default_position;
                    $active_tool->store();
                }
            }
            $this->redirect("course/contentmodules/trigger", ['cid' => $context->getId(), 'plugin_id' => $module->getPluginId()]);
            return;
        }
        $active_tool = ToolActivation::find([$context->id, Request::int('plugin_id')]);
        $template = $GLOBALS['template_factory']->open('tabs.php');
        $template->navigation = Navigation::getItem('/course');
        Navigation::getItem('/course/admin')->setActive(true);
        $this->render_json([
            'tabs' => $template->render(),
            'position' => $active_tool->position
        ]);
    }

    public function reorder_action()
    {
        $context = Context::get();

        $required_perm = $context->getRangeType() === 'course' ? 'tutor' : 'admin';
        if (!$GLOBALS['perm']->have_studip_perm($required_perm, $context->id)) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $position = 0;
            foreach (Request::getArray('order') as $plugin_id) {
                $tool = ToolActivation::find([$context->getId(), $plugin_id]);
                $tool->position = $position++;
                $tool->store();
            }
            $this->redirect($this->reorderURL());
            return;
        }
        Navigation::getItem('/course/admin')->setActive(true);
        $template = $GLOBALS['template_factory']->open('tabs.php');
        $template->navigation = Navigation::getItem('/course');
        $this->render_json([
            'tabs' => $template->render()
        ]);
    }

    public function change_visibility_action()
    {
        if (!Request::isPost()) {
            throw new AccessDeniedException();
        }
        $context = Context::get();

        $required_perm = $context->getRangeType() === 'course' ? 'tutor' : 'admin';
        if (!$GLOBALS['perm']->have_studip_perm($required_perm, $context->id)) {
            throw new AccessDeniedException();
        }
        $moduleclass = Request::get('moduleclass');
        $module = new $moduleclass;

        $active_tool = ToolActivation::find([$context->id, $module->getPluginId()]);
        $metadata = $active_tool->metadata->getArrayCopy();
        if (Request::bool('visible')) {
            unset($metadata['visibility']);
        } else {
            $metadata['visibility'] = 'tutor';
        }
        $active_tool['metadata'] = $metadata;
        $active_tool->store();

        $this->render_json([
            'visibility' => $active_tool->getVisibilityPermission()
        ]);
    }

    public function tiles_display_action()
    {
        if (Request::isPost()) {
            $GLOBALS['user']->cfg->store(
                'CONTENTMODULES_TILED_DISPLAY',
                Request::get('view') === 'tiles'
            );
        }
        $this->render_nothing();
    }

    public function rename_action($module_id)
    {
        $context = Context::get();

        $required_perm = $context->getRangeType() === 'course' ? 'tutor' : 'admin';
        if (!$GLOBALS['perm']->have_studip_perm($required_perm, $context->id)) {
            throw new AccessDeniedException();
        }
        $this->module = PluginManager::getInstance()->getPluginById($module_id);
        $this->metadata = $this->module->getMetadata();
        PageLayout::setTitle(_('Werkzeug umbenennen'));
        $this->tool = ToolActivation::find([$context->id, $module_id]);
        if (Request::isPost()) {
            $metadata = $this->tool->metadata->getArrayCopy();
            if (!trim(Request::get('displayname')) || Request::submitted('delete')) {
                unset($metadata['displayname']);
            } else {
                $metadata['displayname'] = trim(Request::get('displayname'));
            }
            $this->tool['metadata'] = $metadata;
            $this->tool->store();
            $this->redirect('course/contentmodules/index');
        }
    }

    public function info_action($plugin_id)
    {
        $this->plugin = PluginManager::getInstance()->getPluginById($plugin_id);
        $this->metadata = $this->plugin->getMetadata();
        $this->screenshots = [];

        if (isset($this->metadata['screenshot'])) {
            $screenshots = explode('.', $this->metadata['screenshot']);
            $ext = end($screenshots);
            $title  = str_replace('_', ' ', basename($this->metadata['screenshot'], ".{$ext}"));
            $source = "{$this->plugin->getPluginURL()}/{$this->metadata['screenshot']}";

            $this->screenshots[] = compact('title', 'source');
        }
        if (isset($this->metadata['additionalscreenshots'])) {
            foreach ($this->metadata['additionalscreenshots'] as $picture) {
                $pictures = explode('.', $picture);
                $ext = end($pictures);
                $title  = str_replace('_', ' ', basename($picture, ".{$ext}"));
                $source = "{$this->plugin->getPluginURL()}/{$picture}";

                $this->screenshots[] = compact('title', 'source');
            }
        }
        if (isset($this->metadata['screenshots'])) {
            foreach ($this->metadata['screenshots']['pictures'] as $picture) {
                $title  = $picture['title'];
                $source = "{$this->plugin->getPluginURL()}/{$this->metadata['screenshots']['path']}/{$picture['source']}";
                $this->screenshots[] = compact('title', 'source');
            }
        }

        PageLayout::setTitle(sprintf(_('Informationen über %s'), $this->metadata['displayname']));
    }

    private function getModules(Range $context)
    {
        $list = [];

        foreach (PluginEngine::getPlugins('StudipModule') as $plugin) {
            if (!$plugin->isActivatableForContext($context)) {
                continue;
            }

            if (!$this->sem_class->isModuleAllowed(get_class($plugin))) {
                continue;
            }

            $info = $plugin->getMetadata();

            $plugin_id = $plugin->getPluginId();

            $tool = ToolActivation::find([$context->getRangeId(), $plugin->getPluginId()]);
            $toolname = $info['displayname'] ?? $plugin->getPluginname();
            if ($tool && $tool->metadata['displayname']) {
                $displayname = $tool->getDisplayname() . ' (' . $toolname . ')';
            } else {
                $displayname = $toolname;
            }
            $visibility = $tool ? $tool->getVisibilityPermission() : 'nobody';

            $metadata = $plugin->getMetadata();
            $list[$plugin_id] = [
                'id'          => $plugin_id,
                'moduleclass' => get_class($plugin),
                'position'    => $tool ? $tool->position : null,
                'toolname'    => $toolname,
                'displayname' => $displayname,
                'visibility'  => $visibility,
                'active'      => (bool) $tool,
            ];
            if (!empty($metadata['icon_clickable'])) {
                $list[$plugin_id]['icon'] = $metadata['icon_clickable'] instanceof Icon
                    ? $metadata['icon_clickable']->asImagePath()
                    : Icon::create($plugin->getPluginURL().'/'.$metadata['icon_clickable'])->asImagePath();
            } elseif (!empty($metadata['icon'])) {
                $list[$plugin_id]['icon'] = $metadata['icon'] instanceof Icon
                    ? $metadata['icon']->asImagePath()
                    : Icon::create($plugin->getPluginURL().'/'.$metadata['icon'])->asImagePath();
            } else {
                $list[$plugin_id]['icon'] = null;
            }
            $list[$plugin_id]['summary'] = $metadata['summary'] ?? null;
            $list[$plugin_id]['mandatory'] = $this->sem_class->isModuleMandatory(get_class($plugin));
            $list[$plugin_id]['highlighted'] = (bool) $plugin->isHighlighted();
            $list[$plugin_id]['highlight_text'] = $plugin->getHighlightText();
            $list[$plugin_id]['category'] = $metadata['category'] ?? null;
        }

        return $list;
    }
}
