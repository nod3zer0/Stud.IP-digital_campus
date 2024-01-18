<?php

namespace Studip\Forms;

class RangeInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/range_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->min = $this->attributes['min'] ?? null;
        $template->max = $this->attributes['max'] ?? null;
        $template->step = $this->attributes['step'] ?? null;
        $template->required = $this->required;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        return $template->render();
    }
}
