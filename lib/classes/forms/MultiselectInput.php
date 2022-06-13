<?php

namespace Studip\Forms;

class MultiselectInput extends Input
{
    public function render()
    {
        $options = $this->extractOptionsFromAttributes($this->attributes);

        $name = $this->name;
        if (substr($name, -2) === '[]') {
            $name .= substr($name, 0, -2);
        }

        $template = $GLOBALS['template_factory']->open('forms/multiselect_input');
        $template->title = $this->title;
        $template->name = $name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        $template->options = $options;
        return $template->render();
    }

    public function getRequestValue()
    {
        return \Request::getArray($this->name);
    }
}
