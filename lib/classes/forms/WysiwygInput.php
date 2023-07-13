<?php

namespace Studip\Forms;

class WysiwygInput extends Input
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/wysiwyg_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->attributes = arrayToHtmlAttributes($this->attributes);
        return $template->render();
    }

    public function getRequestValue()
    {
        $value = \Request::get($this->name);
        if (trim($value)) {
            return \Studip\Markup::markAsHtml(
                \Studip\Markup::purifyHtml($value)
            );
        } else {
            return '';
        }
    }
}
