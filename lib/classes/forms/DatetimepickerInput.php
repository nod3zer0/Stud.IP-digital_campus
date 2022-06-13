<?php

namespace Studip\Forms;

class DatetimepickerInput extends Input
{
    public function render()
    {
        $attributes = "";
        foreach ((array) $this->attributes as $key => $value) {
            if (in_array($key, ['mindate', 'maxdate'])) {
                $key = ":".$key;
            }
            $attributes .= " ".$key.'="'.htmlReady($value).'"';
        }
        $template = $GLOBALS['template_factory']->open('forms/datetimepicker_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $this->value;
        $template->id = md5(uniqid());
        $template->required = $this->required;
        $template->attributes = $attributes;
        return $template->render();
    }
}
