<?php

use \Studip\Activity\ActivityProvider;

class ActivityfeedController extends AuthenticatedController
{
    public function save_action()
    {
        if (Config::get()->ACTIVITY_FEED === NULL) {
            Config::get()->create('ACTIVITY_FEED', [
                'range' => 'user',
                'type' => 'array',
                'description' => 'Einstellungen des Activity-Widgets']
            );
        }

        $provider = Request::getArray('provider');

        WidgetHelper::addWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FEED', $provider);

        $this->response->add_header('X-Dialog-Close', 1);
        $this->response->add_header('X-Dialog-Execute', 'STUDIP.ActivityFeed.updateFilter');

        $this->render_json($provider);
    }

    /**
     * return a list for all providers for every context
     *
     * @return array
     */
    private function getAllModules()
    {
        $modules = [];

        $modules['system'] = [
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber')
        ];

        $modules[Context::COURSE] = [
            'forum'        => _('Forum'),
            'participants' => _('Teilnehmende'),
            'documents'    => _('Dateien'),
            'wiki'         => _('Wiki'),
            'schedule'     => _('Ablaufplan'),
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber'),
            'courseware'   => _('Courseware')
        ];

        $modules[Context::INSTITUTE] = $modules[Context::COURSE];
        unset($modules[Context::INSTITUTE]['participants']);
        unset($modules[Context::INSTITUTE]['schedule']);

        $standard_plugins = PluginManager::getInstance()->getPlugins("StandardPlugin");
        foreach ($standard_plugins as $plugin) {
            if ($plugin instanceof ActivityProvider) {
                $modules[Context::COURSE][$plugin->getPluginName()] = $plugin->getPluginName();
                $modules[Context::INSTITUTE][$plugin->getPluginName()] = $plugin->getPluginName();
            }
        }

        $modules[Context::USER] = [
            'message'      => _('Nachrichten'),
            'news'         => _('Ankündigungen'),
            'blubber'      => _('Blubber'),
        ];

        $homepage_plugins = PluginEngine::getPlugins('HomepagePlugin');
        foreach ($homepage_plugins as $plugin) {
            if ($plugin->isActivated($GLOBALS['user']->id, 'user')) {
                if ($plugin instanceof ActivityProvider) {
                    $modules[Context::USER][] = $plugin;
                }
            }
        }

        return $modules;
    }

    public function configuration_action()
    {
        $this->config = WidgetHelper::getWidgetUserConfig($GLOBALS['user']->id, 'ACTIVITY_FEED');
        $this->modules = $this->getAllModules();
        $this->context_translations = [
            Context::COURSE    => _('Veranstaltungen'),
            Context::INSTITUTE => _('Einrichtungen'),
            Context::USER      => _('Persönlich'),
            'system'            => _('Global')
        ];

        PageLayout::setTitle(_('Aktivitäten konfigurieren'));
    }
}
