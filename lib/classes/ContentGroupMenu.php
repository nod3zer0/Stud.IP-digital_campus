<?php
/**
 * This class represents the a more flexible menu used to group actions.
*
* @author  Timo Hartge <hartge@data-quest.de>
* @license GPL2 or any later version
* @since   Stud.IP 4.0
*/
class ContentGroupMenu
{
    const THRESHOLD = 1;

    /**
     * Returns an instance.
     *
     * @return ContentGroupMenu
     */
    public static function get()
    {
        return new self();
    }

    private $actions       = [];
    private $condition_all = null;
    private $condition     = true;

    private $image   = null;
    private $image_link_attributes = [];
    private $label;
    private $aria_label;

    private $attributes = [];


    /**
     * Private constructur.
     *
     * @see ContentGroupMenu::get()
     */
    private function __construct()
    {
        $this->label = _('Aktionen');
        $this->aria_label = _('Aktionsmenü');

        $this->addCSSClass('action-menu');
    }

    /**
     * Set condition for the next added item. If condition is false,
     * the item will not be added.
     *
     * @param bool $state State of the condition
     * @return ContentGroupMenu instance to allow chaining
     */
    public function condition($state)
    {
        $this->condition = (bool)$state;

        return $this;
    }

    /**
     * Set condition for all the next added items. If condition is false,
     * no items will be added.
     *
     * @param bool $state State of the condition
     * @return ContentGroupMenu instance to allow chaining
     */
    public function conditionAll($state)
    {
        $this->condition_all = $state;

        return $this;
    }

    /**
     * Checks the condition. Takes global and local (conditionAll() &
     * condition()) conditions into account.
     *
     * @return bool indicating whether the condition is met or not
     */
    protected function checkCondition()
    {
        $result = $this->condition;
        if ($this->condition_all !== null) {
            $result = $result && $this->condition_all;
        }

        $this->condition = true;

        return $result;
    }

    /**
     * Adds a link to the list of actions.
     *
     * @param String $link       Link target
     * @param String $label      Textual representation of the link
     * @param mixed  $icon       Optional icon (as Icon object)
     * @param array  $attributes Optional attributes to add to the <a> tag
     * @return ContentGroupMenu instance to allow chaining
     */
    public function addLink($link, $label, Icon $icon = null, array $attributes = [])
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'       => 'link',
                'link'       => $link,
                'icon'       => $icon,
                'label'      => $label,
                'attributes' => $attributes,
            ];
        }

        return $this;
    }

    /**
     * Adds a button to the list of actions.
     *
     * @param String $name       Button name
     * @param String $label      Textual representation of the name
     * @param mixed  $icon       Optional icon (as Icon object)
     * @param array  $attributes Optional attributes to add to the <a> tag
     * @return ContentGroupMenu instance to allow chaining
     */
    public function addButton($name, $label, Icon $icon = null, array $attributes = [])
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'       => 'button',
                'name'       => $name,
                'icon'       => $icon,
                'label'      => $label,
                'attributes' => $attributes,
            ];
        }

        return $this;
    }

    /**
     * Adds a MultiPersonSearch object to the list of actions.
     *
     * @param MultiPersonSearch $mp MultiPersonSearch object
     * @return ContentGroupMenu instance to allow chaining
     */
    public function addMultiPersonSearch(MultiPersonSearch $mp)
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'   => 'multi-person-search',
                'object' => $mp,
            ];
        }

        return $this;
    }

    /**
     * Adds a css classs to the root element in html.
     *
     * @param string $class Name of the css class
     */
    public function addCSSClass($class)
    {
        $this->addAttribute('class', $class, true);
    }

    /**
     * Adds an attribute to the root element in html.
     *
     * @param string  $key    Name of the attribute
     * @param string  $value  Value of the attribute
     * @param boolean $append Whether a current value should be append or not.
     */
    public function addAttribute($key, $value, $append = false)
    {
        if (isset($this->attributes[$key]) && $append) {
            $this->attributes[$key] .= " {$value}";
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Renders the action menu. If no item was added, an empty string will
     * be returned. If a single item was added, the item itself will be
     * displayed. Otherwise the whole menu will be rendered.
     *
     * @return String containing the html representation of the action menu
     */
    public function render()
    {
        if (count($this->actions) === 0) {
            return '';
        }

        $template_file = 'shared/contentgroup-row.php';
        $template = $GLOBALS['template_factory']->open($template_file);
        $template->actions = $this->actions;

        $has_link_icons = false;
        foreach ($this->actions as $action) {
            if (!empty($action['icon'])) {
                $has_link_icons = true;
                break;
            }
        }
        $template->has_link_icons = $has_link_icons;

        if ($this->image) {
            $template->image = $this->image;
            $template->image_link_attributes = $this->image_link_attributes;
        } else {
            $template->image = "<div></div><div></div><div></div>";
        }

        $template->label = $this->label;
        $template->aria_label = $this->aria_label;

        $template->attributes = $this->attributes;

        return $template->render();
    }

    /**
     * Sets the icon of the menu.
     *
     * @param String $menu_image image html for the menu
     *
     * @param array $image_link_attributes Additional HTML attributes for the link that surrounds the image.
     */
    public function setIcon($menu_image, array $image_link_attributes = [])
    {
        $this->image = $menu_image;
        $this->image_link_attributes = $image_link_attributes;
    }

    /**
     * Sets the label of the menu.
     *
     * @param String $label label for the menu
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Sets the label of the menu.
     *
     * @param String $label label for the menu
     */
    public function setAriaLabel($label)
    {
        $this->aria_label = $label;
    }

    /**
     * Return the number of menu actions.
     *
     * @return integer count actions
     */
    public function countLinks()
    {
        return count($this->actions);
    }
}
