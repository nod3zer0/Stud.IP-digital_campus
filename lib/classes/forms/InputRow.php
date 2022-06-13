<?php

namespace Studip\Forms;

class InputRow extends Part
{
    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/input_row');
        $template->parts = $this->parts;
        return $template->render();
    }
}
