<?php

namespace Studip\Forms;

class CalculatorInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/calculator_input');
        $template->title = $this->title;
        $template->value = $this->value;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        return $template->render();
    }

    public function getAllInputNames()
    {
        return [];
    }
}
