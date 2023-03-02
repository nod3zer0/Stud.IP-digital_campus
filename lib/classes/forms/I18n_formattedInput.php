<?php

namespace Studip\Forms;

class I18n_formattedInput extends Input
{
    public function render()
    {
        if (!isset($this->attributes['id'])) {
            $id = md5(uniqid());
            $this->attributes['id'] = $id;
        } else {
            $id = $this->attributes['id'];
        }
        if (!is_object($this->value)) {
            $value = $this->value;
        } else {
            $value = [\I18NString::getDefaultLanguage() => $this->value->original()];
            $value = array_merge($value, $this->value->toArray());
            $value = array_map(function ($item) {
                return $item ?? '';
            }, $value);
            $value = json_encode($value);
        }

        $template = $GLOBALS['template_factory']->open('forms/i18n_formatted_input');
        $template->title = $this->title;
        $template->name = $this->name;
        $template->value = $value;
        $template->id = $id;
        $template->required = $this->required;
        $template->attributes = $this->attributes;
        return $template->render();
    }

    public function getAllInputNames()
    {
        $all_names = [$this->getName()];
        if (is_object($this->value)) {
            foreach (\Config::get()->CONTENT_LANGUAGES as $lang_id => $language) {
                if (\I18NString::getDefaultLanguage() !== $lang_id) {
                    $all_names[] = $this->getName() . '_i18n[' . $lang_id . ']';
                }
            }
        }
        return $all_names;
    }

    public function getRequestValue()
    {
        return \Studip\Markup::purifyHtml(\Request::i18n($this->name));
    }
}
