<?php

namespace Studip\Forms;

class RadioInput extends Input
{
    public function render()
    {
        $options = $this->extractOptionsFromAttributes($this->attributes);
        $template = $GLOBALS['template_factory']->open('forms/radio_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->options = $options;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        $template->orientation = $this->attributes['orientation'];
        return $template->render();

    }
}
