<?php
/**
 * This class represents the action menu used to group actions.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 3.5
 */
class ActionMenu
{
    const TEMPLATE_FILE_SINGLE   = 'shared/action-menu-single.php';
    const TEMPLATE_FILE_MULTIPLE = 'shared/action-menu.php';

    const RENDERING_MODE_ICONS = 'icons';
    const RENDERING_MODE_MENU = 'menu';

    /**
     * Returns an instance.
     *
     * @return ActionMenu
     */
    public static function get()
    {
        return new self();
    }

    private $actions = [];
    private $attributes = [];
    private $title;

    private $condition_all = null;
    private $condition     = true;

    /**
     * @var string $context The context for the action menu.
     */
    protected $context = '';

    /**
     * @var string|null $rendering_mode The forced rendering mode
     */
    protected $rendering_mode = null;

    /**
     * Private constructur.
     *
     * @see ActionMenu::get()
     */
    private function __construct()
    {
        $this->addCSSClass('action-menu');
        $this->setTitle(_('Aktionen'));
    }

    /**
     * Set condition for the next added item. If condition is false,
     * the item will not be added.
     *
     * @param bool $state State of the condition
     * @return ActionMenu instance to allow chaining
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
     * @return ActionMenu instance to allow chaining
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
     * Returns the number of action menu items (not counting separators).
     *
     * @return int count
     */
    protected function countActions(): int
    {
        return count(array_filter($this->actions, function($action) {
            return $action['type'] !== 'separator';
        }));
    }

    /**
     * Adds a link to the list of actions.
     *
     * @param String|StudipLink  $url        Link target, eithe as string or
     *                                       Stud.IP link. In the latter case,
     *                                       all other parameters are ignored.
     * @param String|array       $label      Textual representation of the link
     * @param mixed              $icon       Optional icon (as Icon object)
     * @param array              $attributes Optional attributes to add to the <a> tag
     * @param mixed              $index      Optional index to access this link (remove for example) afterwards
     * @param mixed              $before     Optional index to insert this link before the link with given index.
     * @return ActionMenu instance to allow chaining
     */
    public function addLink($url, $label = "", Icon $icon = null, array $attributes = [], $index = null, $before = null)
    {
        if ($this->checkCondition()) {
            if ($url instanceof StudipLink) {
                $action = [
                    'type'       => 'link',
                    'link'       => $url->link,
                    'icon'       => $url->icon,
                    'label'      => $url->title,
                    'attributes' => $url->attributes,
                ];
            } else {
                $action = [
                    'type'       => 'link',
                    'link'       => $url,
                    'icon'       => $icon,
                    'label'      => $label,
                    'attributes' => $attributes,
                ];
            }

            $index = $index ?: md5($action['link'] . json_encode($action['attributes']));
            $action['index'] = $index;
            //now insert it possibly at the desired position:
            $before_key = null;
            if ($before) {
                $before_key = $this->getKeyForIndex($before);
                if ($before_key !== null) {
                    array_splice($this->actions, $before_key, 0, $action);
                    return $this;
                }
            }
            $current_key = $this->getKeyForIndex($index);
            if ($current_key !== null) {
                $this->actions[$current_key] = $action;
                return $this;
            }

            $this->removeLink($index);
            $this->actions[] = $action;
        }

        return $this;
    }

    /**
     * Tries to remove the link with the given index and returns true on success (else false)
     * @param $index : the index of the link. If the link had no special
     *                 index it's md5($url.json_encode($ttributes)).
     * @return bool : true if link was removed, false if index didn't exist
     */
    public function removeLink($index)
    {
        $key = $this->getKeyForIndex($index);
        if ($key !== null) {
            unset($this->actions[$key]);
            return true;
        }
        return false;
    }

    /**
     * Adds a button to the list of actions.
     *
     * @param String $name       Button name
     * @param String $label      Textual representation of the name
     * @param mixed  $icon       Optional icon (as Icon object)
     * @param array  $attributes Optional attributes to add to the <a> tag
     * @return ActionMenu instance to allow chaining
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
     * Adds a checkbox to the list of actions.
     *
     * @param String $name       Input name
     * @param String $label      Label displayed in the menu
     * @param bool   $checked    Checked state of the action
     * @param array  $attributes Optional attributes to add to the button
     * @return ActionMenu instance to allow chaining
     */
    public function addCheckbox($name, $label, bool $checked, array $attributes = [])
    {
        return $this->addButton(
            $name,
            $label,
            Icon::create($checked ? 'checkbox-checked' : 'checkbox-unchecked'),
            $attributes + ['role' => 'checkbox', 'aria-checked' => $checked ? 'true' : 'false']
        );
    }

    /**
     * Adds a radio button to the list of actions.
     *
     * @param String $name       Input name
     * @param String $label      Label displayed in the menu
     * @param bool   $checked    Checked state of the action
     * @param array  $attributes Optional attributes to add to the button
     * @return ActionMenu instance to allow chaining
     */
    public function addRadioButton($name, $label, bool $checked, array $attributes = [])
    {
        return $this->addButton(
            $name,
            $label,
            Icon::create($checked ? 'radiobutton-checked' : 'radiobutton-unchecked'),
            $attributes + ['role' => 'radio', 'aria-checked' => $checked ? 'true' : 'false']
        );
    }

    /**
     * Adds a MultiPersonSearch object to the list of actions.
     *
     * @param MultiPersonSearch $mp MultiPersonSearch object
     * @return ActionMenu instance to allow chaining
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
     * Adds a separator line to the list of actions.
     *
     * @return ActionMenu instance to allow chaining
     */
    public function addSeparator(): ActionMenu
    {
        if ($this->checkCondition()) {
            $this->actions[] = [
                'type'   => 'separator',
            ];
        }

        return $this;
    }

    /**
     * Adds a css classs to the root element in html.
     *
     * @param string $class Name of the css class
     * @return ActionMenu instance to allow chaining
     */
    public function addCSSClass($class)
    {
        $this->addAttribute('class', $class, true);

        return $this;
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
        if ($this->countActions() === 0) {
            return '';
        }

        $template_file = $this->getRenderingMode() === self::RENDERING_MODE_ICONS
                       ? self::TEMPLATE_FILE_SINGLE
                       : self::TEMPLATE_FILE_MULTIPLE;
;
        $template = $GLOBALS['template_factory']->open($template_file);
        $template->actions = array_map(function ($action): array {
            $action['disabled'] = isset($action['attributes']['disabled'])
                     && $action['attributes']['disabled'] !== false;
            if ($action['disabled'] && $action['icon']) {
                $action['icon'] = $action['icon']->copyWithRole(Icon::ROLE_INACTIVE);
            }
            return $action;
        }, $this->actions);
        $template->title = $this->title;
        $template->action_menu_title = $this->generateTitle();
        $template->attributes = $this->attributes;
        return $template->render();
    }

    /**
     * Magic method to render the menu as a string.
     *
     * @return String containing the html representation of the action menu
     * @see ActionMenu::render()
     */
    public function __toString()
    {
        return $this->render();
    }

    protected function getKeyForIndex($index)
    {
        foreach ($this->actions as $key => $value) {
            if (!empty($value['index']) && $value['index'] === $index) {
                return $key;
            }
        }
        return null;
    }

    /**
     * Sets the context for the menu.
     *
     * @param string $context The context to be set.
     *
     * @return ActionMenu The action menu instance (to allow chaining).
     */
    public function setContext(string $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Forces an explicit rendering mode.
     *
     * @param string|null $mode The desired rendering mode or null for automatic detection
     * @return ActionMenu The action menu instance to allow chaining
     * @throws Exception
     */
    public function setRenderingMode(?string $mode): ActionMenu
    {
        if (
            $mode !== null
            && $mode !== self::RENDERING_MODE_ICONS
            && $mode !== self::RENDERING_MODE_MENU
        ) {
            throw new Exception("Invalid rendering mode '{$mode}'");
        }

        $this->rendering_mode = $mode;

        return $this;
    }

    /**
     * Returns the rendering mode for this action menu. This is set by either
     * calling setRenderingMode or automatically determined by the configured
     * threshold.
     *
     * @return string
     */
    public function getRenderingMode(): string
    {
        $rendering_mode = $this->rendering_mode;

        if ($rendering_mode === null) {
            $rendering_mode = $this->countActions() <= Config::get()->ACTION_MENU_THRESHOLD
                            ? self::RENDERING_MODE_ICONS
                            : self::RENDERING_MODE_MENU;
        }

        return $rendering_mode;
    }

    /**
     * Sets the title for the action menu.
     *
     * @param string $title display title
     *
     * @return ActionMenu instance to allow chaining
     */
    public function setTitle(string $title): ActionMenu
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Generates the title of the action menu, including its context, if the context has been set.
     *
     * @return string The title of the action menu.
     */
    public function generateTitle() : string
    {
        if ($this->context) {
            return sprintf(
                _('%s für %s'),
                $this->title,
                $this->context
            );
        } else {
            return $this->title;
        }
    }
}
