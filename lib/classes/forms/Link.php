<?php
namespace Studip\Forms;

/**
 * The Link class represents a part of a form that displays a link.
 */
class Link extends Part
{
    protected $url;
    protected $label;
    protected $icon;
    protected $attributes = [];

    public function __construct(string $url, string $label, \Icon $icon = null)
    {
        $this->url = $url;
        $this->label = $label;
        $this->icon = $icon;
    }

    /**
     * Sets the url for the link.
     *
     * @param string $url
     * @return $this
     */
    public function setURL(string $url): Link
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the url for the link.
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * Sets the label for the link.
     *
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): Link
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Returns the label for the link.
     *
     * @return string
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * Sets the icon for the link. May be null to remove the icon.
     *
     * @param \Icon $icon
     * @return $this
     */
    public function setIcon(\Icon $icon = null): Link
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Returns the icon for the link.
     * @return \Icon|null
     */
    public function getIcon(): ?\Icon
    {
        return $this->icon;
    }

    /**
     * Replaces all attributes for the link.
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes(array $attributes): Link
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * Adds/appends attributes to the current attributes for the link.
     *
     * @param array $attributes
     * @return $this
     */
    public function addAttributes(array $attributes): Link
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Sets a single attribute for the link.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setAttribute(string $key, $value): Link
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Returns the attributes for the link.
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Removes an attribute.
     *
     * @param string $key
     * @param bool   $throw_exception Throw an exception if the attribute does not exists (default: false)
     * @return $this
     */
    public function removeAttribute(string $key, bool $throw_exception = false): Link
    {
        if (!isset($this->attributes[$key]) && $throw_exception) {
            throw new \RuntimeException("No attribute {$key} defined");
        }

        if (isset($this->attributes[$key])) {
            unset($this->attributes[$key]);
        }

        return $this;
    }

    /**
     * "Renders" the text: Either return it directly, if it is HTML or call htmlReady first before returning it.
     *
     * @return string The text that shall be placed in the form, either as HTML or plain text.
     */
    public function render()
    {
        return sprintf(
            '<div class="formpart"><a href="%1$s" %2$s>%3$s %4$s</a></div>',
            \URLHelper::getLink($this->url, [], true),
            arrayToHtmlAttributes($this->attributes),
            $this->icon ? $this->icon->asImg(['class' => 'text-bottom']) : '',
            htmlReady($this->label)
        );
    }
}
