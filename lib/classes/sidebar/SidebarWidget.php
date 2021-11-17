<?php
/**
 * A widget for the sidebar.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL 2 or later
 * @since   3.1
 * @see     Sidebar
 */
class SidebarWidget extends Widget
{
    protected $id = '';


    public function __construct()
    {
        $this->layout = 'sidebar/widget-layout.php';
    }


    /**
     * Sets the ID of the HTML element that represents the widget.
     *
     * @param $id The element-ID to be used for the widget.
     *
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }


    /**
     * Returns the ID of this widget, if it is set.
     *
     * @return string The ID of the widget or an empty string, if it is not set.
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * Sets the title of the widget.
     *
     * @param String $title The title of the widget
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the title of the widget
     *
     * @return mixed The title of the widget of false if no title has been set
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Removes the title of the widget.
     */
    public function removeTitle()
    {
        $this->title = false;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function removeExtra()
    {
        $this->extra = false;
    }

    /**
     * Renders the widget.
     * The widget will only be rendered if it contains at least one element.
     *
     * @return String The THML code of the rendered sidebar widget
     */
    public function render($variables = [])
    {
        if ($this->id) {
            $this->template_variables['id'] = $this->id;
        }
        return parent::render($variables);
    }
}
