<?php

use Studip\OAuth2\Container;
use Studip\OAuth2\Models\Client;
use Studip\OAuth2\SetupInformation;

class Admin_Oauth2Controller extends AuthenticatedController
{
    /**
     * @param string $action
     * @param string[] $args
     *
     * @return void
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');

        Navigation::activateItem('/admin/config/oauth2');
        PageLayout::setTitle(_('OAuth2 Verwaltung'));

        $this->types = [
            'website' => _('Website'),
            'desktop' => _('Herkömmliches Desktopprogramm'),
            'mobile' => _('Mobile App'),
        ];

        // Sidebar
        $views = new ViewsWidget();
        $views->addLink(
            _('Übersicht'),
            $this->indexURL()
        )->setActive($action === 'index');
        Sidebar::get()->addWidget($views);

        $this->container = new Container();
    }

    public function index_action(): void
    {
        $this->setup = $this->container->get(SetupInformation::class);
        $this->clients = Client::findBySql('1 ORDER BY chdate DESC');
    }
}
