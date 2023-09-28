<?php

class SeparatorElement extends WidgetElement
{
    public $attributes = [];

    /**
     * create a separator element
     *
     * @param array  $attributes HTML-attributes in an associative array.
     */
    public function __construct($attributes = [])
    {
        parent::__construct();

        $this->attributes = $attributes;
    }

    /**
     * Renders the element.
     *
     * @return string
     */
    public function render()
    {
        return sprintf('<hr %s>', arrayToHtmlAttributes($this->attributes));
    }
}
