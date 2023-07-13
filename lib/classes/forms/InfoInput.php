<?php

namespace Studip\Forms;

class InfoInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/info_input');
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
