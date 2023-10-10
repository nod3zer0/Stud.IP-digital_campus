<?php
class ButtonElement extends WidgetElement implements ArrayAccess
{
    use AttributesArrayAccessTrait;

    public $label;
    public $icon = null;

    public function __construct(string $label, \Icon $icon = null, array $attributes = [])
    {
        parent::__construct();

        $this->label = $label;
        $this->icon = $icon;
        $this->attributes = $attributes;
    }

    public function render()
    {
        return sprintf(
            '<button %s>%s</button>',
            arrayToHtmlAttributes($this->attributes),
            htmlReady($this->label)
        );
    }
}
