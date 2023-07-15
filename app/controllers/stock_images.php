<?php

class StockImagesController extends AuthenticatedController
{
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        URLHelper::removeLinkParam('cid');
        $GLOBALS['perm']->check('admin');

        Navigation::activateItem('/admin/locations/stock_images');
        \PageLayout::setTitle(_('Verwaltung des Bilder-Pools'));
        $this->setSidebar();
    }

    /**
     * Administration view for banner
     */
    public function index_action(): void
    {
    }

    /**
     * Setup the sidebar
     */
    protected function setSidebar(): void
    {
        $sidebar = \Sidebar::Get();
        $sidebar->addWidget(new \VueWidget('stock-images-widget'));
    }
}
