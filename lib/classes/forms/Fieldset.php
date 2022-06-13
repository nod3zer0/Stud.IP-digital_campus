<?php

namespace Studip\Forms;

class Fieldset extends Part
{
    protected $legend = null;

    public function __construct($legend = null)
    {
        $this->legend = $legend;
    }

    public function setLegend($legend)
    {
        $this->legend = $legend;
    }

    public function render()
    {
        $template = $GLOBALS['template_factory']->open('forms/fieldset');
        $template->legend = $this->legend;
        $template->parts = $this->parts;
        return $template->render();
    }
}
