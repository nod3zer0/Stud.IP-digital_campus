<?php

class QuickselectionController extends AuthenticatedController
{
    public function save_action()
    {
        if (Config::get()->QUICK_SELECTION === null) {
            Config::get()->create('QUICK_SELECTION', [
                'range'       => 'user',
                'type'        => 'array',
                'description' => 'Einstellungen des QuickSelection-Widgets',
            ]);
        }

        $add_removes = Request::optionArray('add_removes');

        // invert add_removes so that only unchecked values are stored into config
        $names = [];

        $navigation = Navigation::getItem('/start');
        foreach ($navigation as $name => $nav) {
            if (!in_array($name, $add_removes)) {
                $names[$name] = 'deactivated';
            }

        }

        UserConfig::get($GLOBALS['user']->id)->store('QUICK_SELECTION', $names);

        $template = PluginEngine::getPlugin('QuickSelection')->getPortalTemplate();

        $this->response->add_header('X-Dialog-Close', 1);
        $this->response->add_header('X-Dialog-Execute', 'STUDIP.QuickSelection.update');

        $this->render_template($template);
    }

    public function configuration_action()
    {
        $this->links = Navigation::getItem('/start');
        $this->config = UserConfig::get($GLOBALS['user']->id)->getValue('QUICK_SELECTION');

        PageLayout::setTitle(_('Schnellzugriff konfigurieren'));
    }
}
