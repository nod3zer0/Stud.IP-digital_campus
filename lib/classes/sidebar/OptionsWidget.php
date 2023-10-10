<?php
/**
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */
class OptionsWidget extends ListWidget
{
    const INDEX = 'options';

    /**
     * @param string|null $title Optional alternative title
     */
    public function __construct(?string $title = null)
    {
        parent::__construct();

        $this->addCSSClass('widget-options');
        $this->title = $title ?: _('Einstellungen');
    }

    /**
     * @param string      $label
     * @param bool        $state
     * @param string      $toggle_url     Url to execute the action
     * @param string|null $toggle_url_off Optional alternative url to explicitely
     *                                    turn off the checkbox ($toggle_url will
     *                                    then act as $toggle_url_on)
     * @param Array       $attributes     Optional additional attributes for the anchor
     *
     * @return ButtonElement
     */
    public function addCheckbox(
        string $label,
        $state,
        string $toggle_url,
        ?string $toggle_url_off = null,
        array $attributes = []
    ): ButtonElement {
        // TODO: Remove this some versions after 5.0
        $toggle_url = html_entity_decode($toggle_url);
        $toggle_url_off = isset($toggle_url_off) ? html_entity_decode($toggle_url_off) : null;

        $attributes['class'] = trim(($attributes['class'] ?? '') . ' options-checkbox options-' . ($state ? 'checked' : 'unchecked'));

        return $this->addElement(
            new ButtonElement($label, null, $attributes + [
                'aria-checked' => $state ? 'true' : 'false',
                'formaction'   => $state && $toggle_url_off !== null ? $toggle_url_off : $toggle_url,
                'role'         => 'checkbox',
            ])
        );
    }

    /**
     * Adds a radio button to the widget.
     *
     * @param string $label
     * @param string $url
     * @param bool   $checked
     * @param array $attributes
     *
     * @return ButtonElement
     */
    public function addRadioButton(
        string $label,
        string $url,
        $checked = false,
        array $attributes = []
    ): ButtonElement {
        // TODO: Remove this some versions after 5.0
        $url = html_entity_decode($url);

        $attributes['class'] = trim(($attributes['class'] ?? '') . ' options-radio options-' . ($checked ? 'checked' : 'unchecked'));

        return $this->addElement(
            new ButtonElement($label, null, $attributes + [
                'aria-checked' => $checked ? 'true' : 'false',
                'formaction'   => $url,
                'role'         => 'radio',
            ])
        );
    }

    /**
     * Adds a select element to the widget.
     *
     * @param string $label
     * @param string $url
     * @param string $name            Attribute name
     * @param array  $options         Array of associative options (value => label)
     * @param mixed  $selected_option Currently selected option
     * @param array  $attributes      Additional attributes
     */
    public function addSelect(
        string $label,
        $url,
        string $name,
        array $options,
        $selected_option = false,
        array $attributes = []
    ): SelectListElement {
        $attributes['data-formaction'] = $url;

        return $this->addElement(
            new SelectListElement($label, $name, $options, $selected_option, $attributes)
        );
    }
}
