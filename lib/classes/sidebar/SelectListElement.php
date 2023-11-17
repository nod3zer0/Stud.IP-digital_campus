<?php
class SelectListElement extends WidgetElement implements ArrayAccess
{
    use AttributesArrayAccessTrait;

    protected $label;
    protected $name;
    protected $options;
    protected $selected_option;
    protected $submit_upon_select;

    public function __construct(
        string $label,
        string $name,
        array $options,
        $selected_option = null,
        array $attributes = [],
        bool $submit_upon_select = true
    ) {
        $this->label = $label;
        $this->name = $name;
        $this->options = $options;
        $this->selected_option = $selected_option;
        $this->attributes = $attributes;
        $this->submit_upon_select = $submit_upon_select;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function render()
    {
        $option_content = '';
        $nested_select = '';
        $submit_upon_select = $this->submit_upon_select ? ' submit-upon-select' : '';

        foreach ($this->options as $value => $option) {
            if ($option instanceof SelectElement) {
                if ($option->isHeader() || $option->getIndentLevel() > 0) {
                    $nested_select = ' nested-select';
                }
                $option_attr = [
                    'value' => $option->getId(),
                    'class' => ($option->isHeader() ? 'nested-item-header' : '') . ($option->getIndentLevel() ? ' nested-item' : ''),
                    'title' => $option->getTooltip() ?: $option->getLabel(),
                    'selected' => $option->isActive()
                ];
                $option_label = $option->getLabel();
            } else {
                $option_attr = compact('value');
                $option_label = $option;
            }

            $option_content .= sprintf(
                '<option %s>%s</option>',
                arrayToHtmlAttributes($option_attr),
                htmlReady($option_label)
            );
        }

        $attributes = $this->attributes;
        $attributes['class'] = trim(($attributes['class'] ?? '') . ' sidebar-selectlist' . $submit_upon_select . $nested_select);
        $attributes['name'] = $this->name;
        $attributes['aria-label'] = $this->label;

        return sprintf(
            '<label><div class="label-text" hidden>%s</div><select %s>%s</select></label>',
            htmlReady($this->label),
            arrayToHtmlAttributes($attributes),
            $option_content
        );
    }
}
