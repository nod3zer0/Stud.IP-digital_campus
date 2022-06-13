<?php

namespace Studip\Forms;

class NoInput extends Input
{
    public function render()
    {
        return '';
    }

    public function getAllInputNames()
    {
        return [];
    }
}
