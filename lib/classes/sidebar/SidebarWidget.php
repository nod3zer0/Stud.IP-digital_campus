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
    protected $additional_attributes = [];

    public function __construct()
    {
        $this->layout = 'sidebar/widget-layout.php';
    }

    /**
     * Sets the ID of the HTML element that represents the widget.
     *
     * @param string $id The element-ID to be used for the widget.
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
        return $this->id ?? '';
    }

    /**
     * Sets the title of the widget.
     *
     * @param string $title The title of the widget
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the title of the widget
     *
     * @return string The title of the widget of false if no title has been set
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

    public function setAdditionalAttribute(string $key, $value)
    {
        $this->additional_attributes[$key] = $value;
    }

    public function setAdditionalAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAdditionalAttribute($key, $value);
        }
    }

    public function removeAdditionalAttribute(string $key)
    {
        unset($this->additional_attributes[$key]);
    }

    /**
     * Renders the widget.
     * The widget will only be rendered if it contains at least one element.
     *
     * @return string The THML code of the rendered sidebar widget
     */
    public function render($variables = [])
    {
        $attributes = $this->additional_attributes;
        if (!empty($this->id)) {
            $attributes['id'] = $this->id;
        }
        $variables['additional_attributes'] = $attributes;

        return parent::render($variables);
    }
}
