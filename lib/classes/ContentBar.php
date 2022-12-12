<?php

/**
 * Class ContentBar
 * ContentBar for page, with optional ActionMenu, Table of contents and breadcrumbs.
 *
 * @author Thomas Hackl <hackl@data-quest.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */

class ContentBar
{

    public $infoText = '';
    public $icon = '';
    public $toc = null;
    public $actionMenu = null;

    /**
     * The singleton instance of the container
     */
    protected static $instance = null;

    /**
     * Returns the instance of this container to ensure there is only one
     * instance.
     *
     * @param TOCItem $toc Table of contents object.
     * @param string $info Some information to show, like creation date, author etc.
     * @param Icon|null $icon An icon to show in content bar.
     * @param ActionMenu|null $actionMenu Optional action menu for page actions.
     * @return ContentBar
     * @static
     */
    public static function get(): ContentBar
    {
        if (static::$instance === null) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Private constructor to ensure that the singleton Get() method is always
     * used.
     *
     * @see ContentBar::get
     */
    protected function __construct()
    {
    }

    /**
     * Provide some info text.
     * @param string $info
     * @return ContentBar $this Return current instance for method chaining.
     */
    public function setInfo(string $info)
    {
        $this->infoText = $info;
        return $this;
    }

    /**
     * Set an icon.
     * @param Icon $icon
     * @return ContentBar $this Return current instance for method chaining.
     */
    public function setIcon(Icon $icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Set a table of contents object.
     * @param TOCItem $toc
     * @return ContentBar $this Return current instance for method chaining.
     */
    public function setTOC(TOCItem $toc)
    {
        $this->toc = $toc;
        return $this;
    }

    /**
     * Provide an action menu.
     * @param ActionMenu $actionMenu
     * @return ContentBar $this Return current instance for method chaining.
     */
    public function setActionMenu(ActionMenu $actionMenu)
    {
        $this->actionMenu = $actionMenu;
        return $this;
    }

    /**
     * Render the content bar from corresponding template
     * @return string
     */
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('contentbar/contentbar');
        $template->actionMenu = $this->actionMenu;
        $template->info = $this->infoText;

        // Table of contents
        $tocTemplate = $GLOBALS['template_factory']->open('toc/generic-toc-list');
        $tocTemplate->set_attribute('root', $this->toc);
        $template->toc = $this->toc;
        $template->ttpl = $tocTemplate;

        // Breadcrumbs
        $brdcrmb = $GLOBALS['template_factory']->open('toc/generic-toc-breadcrumb');
        $brdcrmb->set_attribute('item', $this->toc->getActiveItem() ?: $this->toc);
        $template->breadcrumbs = $brdcrmb;

        $template->icon = $this->icon;
        return $template->render();
    }

    /**
     * Magic method: when ContentBar is used as a string, this will just call
     * the render method, returning a string representation of this object.
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

}
