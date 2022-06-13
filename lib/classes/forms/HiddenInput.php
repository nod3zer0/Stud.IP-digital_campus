<?php

namespace Studip\Forms;

class HiddenInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/hidden_input');
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        return $template->render();
    }
}
