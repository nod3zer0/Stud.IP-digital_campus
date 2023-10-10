<?php
class SelectListElement extends WidgetElement implements ArrayAccess
{
    use AttributesArrayAccessTrait;

    protected $label;
    protected $name;
    protected $options;
    protected $selected_option;

    public function __construct(
        string $label,
        string $name,
        array $options,
        $selected_option = null,
        array $attributes = []
    ) {
        $this->label = $label;
        $this->name = $name;
        $this->options = $options;
        $this->selected_option = $selected_option;
        $this->attributes = $attributes;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function render()
    {
        $option_content = '';

        foreach ($this->options as $value => $option) {
            $selected = $value == $this->selected_option ? 'selected' : '';
            $option_content .= sprintf(
                '<option value="%s" %s>%s</option>',
                htmlReady($value),
                $selected,
                htmlReady($option)
            );
        }

        $attributes = $this->attributes;
        $attributes['class'] = trim(($attributes['class'] ?? '') . ' sidebar-selectlist submit-upon-select');
        $attributes['name'] = $this->name;
        $attributes['aria-label'] = $this->label;

        return sprintf(
            '<select %s>%s</select>',
            arrayToHtmlAttributes($attributes),
            $option_content
        );
    }
}
