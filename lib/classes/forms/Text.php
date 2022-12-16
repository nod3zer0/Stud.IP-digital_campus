<?php

namespace Studip\Forms;

/**
 * The Text class represents a part of a form that just displays text.
 * The text can either be HTML or unformatted text.
 */
class Text extends Part
{
    /**
     * The text to be displayed.
     */
    protected $text = '';

    /**
     * This attribute defines whether to interpret the text as HTML (true) or as plain text (false).
     */
    protected $text_is_html = true;

    /**
     * Sets the text that shall be displayed in this form part.
     *
     * @param string $text The text to be displayed.
     * @param bool $text_is_html Whether the text is HTML (true) or plain text. Defaults to true.
     * @return $this This form part.
     */
    public function setText(string $text, bool $text_is_html = true): Text
    {
        $this->text = $text;
        $this->text_is_html = $text_is_html;
        return $this;
    }

    /**
     * @return string The "raw form" of the text that shall be displayed.
     */
    public function getText() : string
    {
        return $this->text;
    }

    /**
     * @return bool Whether the text is HTML (true) or not (false).
     */
    public function isHtmlText() : bool
    {
        return $this->text_is_html;
    }

    /**
     * "Renders" the text: Either return it directly, if it is HTML or call htmlReady first before returning it.
     *
     * @return string The text that shall be placed in the form, either as HTML or plain text.
     */
    public function render()
    {
        if ($this->text_is_html) {
            return $this->text;
        } else {
            return htmlReady($this->text);
        }
    }

    /**
     * @see Text::render()
     */
    public function renderWithCondition()
    {
        return $this->render();
    }
}
