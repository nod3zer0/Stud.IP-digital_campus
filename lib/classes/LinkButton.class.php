<?php
/*
 * Copyright (c) 2011 mlunzena@uos.de, aklassen@uos.de
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


namespace Studip;

/**
 * Represents an HTML link element.
 */
class LinkButton extends Interactable
{
    /**
     * Initialize a Hyperlink used as button.
     * The second parameter is used as @href attribute of the
     * resulting <a> HTML element.
     *
     * @param string $label       the label of the <a> element
     * @param string $url         the @href element of the <a> element
     * @param array  $attributes  the attributes of the <a> element
     */
    protected function initialize($label, $url, $attributes)
    {
        $this->attributes['href'] = $url ?: \URLHelper::getURL();
    }

    /**
     * @return string returns a HTML representation of this hyperlink.
     */
    public function __toString()
    {
        if (
            isset($this->attributes['disabled'])
            && $this->attributes['disabled'] !== false
        ) {
            return (string) Button::create($this->label, 'none', $this->attributes);
        }

        // add "button" to attribute @class
        if (empty($this->attributes['class'])) {
            $this->attributes['class'] = '';
        }
        $this->attributes['class'] .= ' button';

        // TODO: URLHelper...?!
        return sprintf(
            '<a %s>%s</a>',
            arrayToHtmlAttributes($this->attributes),
            htmlReady($this->label)
        );
    }
}
