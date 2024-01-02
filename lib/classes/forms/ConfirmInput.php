<?php

namespace Studip\Forms;

class ConfirmInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/confirm_password_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        return $template->render();
    }
}
