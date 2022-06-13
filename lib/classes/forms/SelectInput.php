<?php

namespace Studip\Forms;

class SelectInput extends Input
{
    public function render()
    {
        $options = $this->extractOptionsFromAttributes($this->attributes);

        $template = $GLOBALS['template_factory']->open('forms/select_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        $template->options = $options;
        return $template->render();
    }
}
