<?php

/**
 * Class TOCItem
 * A representation of a single entry in a table of contents or breadcrumb.
 * One entry contains all children.
 *
 * @author Thomas Hackl <hackl@data-quest.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */

class TOCItem
{

    // This item's display title.
    public $title = '';
    // Optional icon.
    public $icon = null;
    // A URL for link handling.
    public $url = '';
    // Additional attributes for URL
    public $link_attributes = [];
    // Parent item.
    public $parent = null;
    // Array of child items.
    public $children = [];
    // Marks the currently active item.
    public $active = false;

    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * Get the display title.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set a new display title.
     * @param string $newTitle
     * @return $this
     */
    public function setTitle(string $newTitle)
    {
        $this->title = $newTitle;
        return $this;
    }

    /**
     * Get this item's icon.
     * @return Icon
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set a new icon.
     * @param Icon $icon
     * @return $this
     */
    public function setIcon(Icon $icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Get this item's URL, with additional parameters if set.
     * @return string
     */
    public function getUrl()
    {
        return count($this->link_attributes) > 0 ?
            URLHelper::getLink($this->url, $this->link_attributes) :
            $this->url;
    }

    /**
     * Set a new URL, optionally with additional link parameters.
     * @param string $newUrl
     * @param mixed|null $parameters
     * @return $this
     */
    public function setUrl(string $newUrl, mixed $parameters = null)
    {
        $this->url = $newUrl;

        if ($parameters !== null) {
            $this->link_attributes = $parameters;
        }

        return $this;
    }

    /**
     * Gets the additional link attributes.
     * @return array
     */
    public function getLinkAttributes()
    {
        return $this->link_attributes;
    }

    /**
     * Sets new additional link attributes.
     * @param array $attributes
     * @return $this
     */
    public function setLinkAttributes($attributes)
    {
        $this->link_attributes = $attributes;
        return $this;
    }

    /**
     * Gets this item's parent element.
     * @return null|TOCItem
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Sets a new parent element for this item.
     * @param TOCItem $newParent
     * @return $this
     */
    public function setParent(TOCItem $newParent)
    {
        $this->parent = $newParent;
        return $this;
    }

    /**
     * Gets this item's children as array.
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Sets a new array of child elements.
     * @param array $newChildren
     * @return $this
     */
    public function setChildren(array $newChildren)
    {
        $this->children = $newChildren;
        return $this;
    }

    /**
     * Add a new child to this item, optionally at a given position.
     *
     * @param TOCItem $newChild
     * @param null|int $position
     * @return $this
     */
    public function addChild(TOCItem $newChild, $position = null)
    {
        $newChild->setParent($this);
        if ($position === null) {
            $this->children[] = $newChild;
        } else {
            $this->children = array_splice($this->children, $position, 0, $newChild);
        }
        return $this;
    }

    /**
     * Removes the given child.
     *
     * @param TOCItem $child
     * @return $this
     */
    public function removeChild(TOCItem $child)
    {
        array_filter($this->children, function ($c) use ($child) {
            return $c != $child;
        });
        return $this;
    }

    /**
     * Does the current item have children?
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->children) > 0;
    }

    /**
     * Count the elements in the hierarchy, starting at (and counting in) current item.
     *
     * @return int
     */
    public function countAllChildren()
    {
        // Current item + all direct children.
        $count = 1;

        foreach ($this->children as $child) {
            $count += $child->countAllChildren();
        }

        return $count;
    }

    /**
     * Check if current item is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * Set active status of current item.
     *
     * @param bool $state
     * @return $this
     */
    public function setActive(bool $state)
    {
        $this->active = $state;
        return $this;
    }

    public function getActiveItem()
    {
        if ($this->isActive()) {
            return $this;
        } else {
            $active = array_filter($this->getRoot()->flatten(), function ($i) {
                return $i->isActive();
            });
            return array_shift($active);
        }
    }

    /**
     * Is the current element the root of its hierarchy?
     *
     * @return bool
     */
    public function isRoot()
    {
        return $this->parent == null;
    }

    /**
     * Get the root element of the current hierarchy (= the element with no parent)
     *
     * @return bool
     */
    public function getRoot()
    {
        return $this->isRoot() ? $this : $this->parent->getRoot();
    }

    /**
     * Get a string representation of the current hierarchy as breadcrumb-like path.
     *
     * @param string $separator which separator tu use between elements?
     * @return string
     */
    public function getPath($separator = '/')
    {
        $path = $this->title;

        if (!$this->isRoot()) {
            $path = $this->parent->getPath($separator) . $separator . ' ' . $path . ' ';
        }

        return $path;
    }

    /**
     * Generates a flat representation of the current item and all its children and children's children.
     * @return array|TOCItem[]
     */
    public function flatten()
    {
        $flat = [$this];
        foreach ($this->getChildren() as $child) {
            $flat = array_merge($flat, $child->flatten());
        }
        return $flat;
    }

    /**
     * String representation of this item.
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

}
