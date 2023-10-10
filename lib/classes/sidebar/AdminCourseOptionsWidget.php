<?php
/**
 * This is a special widget class for use with the admin course page.
 * It will connect to the Vue app and use it's method to change the filters.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @since Stud.IP 5.4
 */
class AdminCourseOptionsWidget extends ListWidget
{
    /**
     * @var string|null
     */
    protected $position_in_sidebar = null;

    public function __construct(string $title)
    {
        parent::__construct();

        $this->addCSSClass('widget-options admin-courses-options');
        $this->title = $title;
    }

    /**
     * Adds a checkbox to the widget.
     *
     * @param string $label
     * @param string $filter_name
     * @param bool   $checked
     * @param        $true_value
     * @param        $false_value
     * @param array  $attributes
     *
     * @return ButtonElement
     */
    public function addCheckbox(
        string $label,
        string $filter_name,
        bool $checked = false,
        $true_value = 1,
        $false_value = null,
        array $attributes = []
    ): ButtonElement {
        if (!isset($attributes['onclick'])) {
            $attributes['onclick'] = implode('', [
                sprintf(
                    'STUDIP.AdminCourses.App.changeFilter({%s: $(this).is(".options-checked") ? %s : %s});',
                    json_encode($filter_name),
                    json_encode($true_value),
                    json_encode($false_value)
                ),
                "return false;",
            ]);
        }

        $attributes['class'] = trim(($attributes['class'] ?? '') . ' options-checkbox options-' . ($checked ? 'checked' : 'unchecked'));

        return $this->addElement(
            new ButtonElement($label, null, $attributes + [
                'aria-checked' => $checked ? 'true' : 'false',
                'role'         => 'checkbox',
            ])
        );
    }

    /**
     * Adds a radio button to the widget.
     *
     * @param string $label
     * @param string $filter_name
     * @param        $value
     * @param bool   $checked
     * @param array  $attributes
     *
     * @return ButtonElement
     */
    public function addRadioButton(
        string $label,
        string $filter_name,
        $value,
        bool $checked = false,
        array $attributes = []
    ): ButtonElement {
        if (!isset($attributes['onclick'])) {
            $attributes['onclick'] = implode('', [
                sprintf(
                    'STUDIP.AdminCourses.App.changeFilter({%s: %s});',
                    json_encode($filter_name),
                    json_encode($value)
                ),
                "return false;",
            ]);
        }

        $attributes['class'] = trim(($attributes['class'] ?? '') . ' options-radio options-' . ($checked ? 'checked' : 'unchecked'));

        return $this->addElement(
            new ButtonElement($label, null, $attributes + [
                'aria-checked'     => $checked ? 'true' : 'false',
                'data-filter-name' => $filter_name,
                'role'             => 'radio',
            ])
        );
    }

    /**
     * Adds a select list to the widget.
     *
     * @param string $label
     * @param string $filter_name
     * @param array  $options
     * @param        $selected_value
     * @param array  $attributes
     */
    public function addSelect(
        string $label,
        string $filter_name,
        array $options,
        $selected_value = null,
        array $attributes = []
    ): SelectListElement {
        if (!isset($attributes['onchange'])) {
            $attributes['onfocus'] = 'this.classList.remove("submit-upon-select");';

            $attributes['onchange'] = sprintf(
                'STUDIP.AdminCourses.App.changeFilter({%s: this.value});',
                json_encode($filter_name)
            );
        }

        return $this->addElement(
            new SelectListElement($label, $filter_name, $options, $selected_value, $attributes)
        );
    }

    /**
     * Sets the position where the widget should be inserted in the sidebar.
     */
    public function setPositionInSidebar(?string $position): void
    {
        $this->position_in_sidebar = $position;
    }

    /**
     * Returns the position where the widget should be inserted in the sidebar.
     *
     * @return string|null
     */
    public function getPositionInSidebar(): ?string
    {
        return $this->position_in_sidebar;
    }
}
