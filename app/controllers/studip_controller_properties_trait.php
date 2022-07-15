<?php
/**
 * This trait manages all variable assignments to the controller and templates.
 *
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since Stud.IP 5.2
 */
trait StudipControllerPropertiesTrait
{
    /**
     * Stores the assigned variables.
     * @var array
     */
    protected $_template_variables = [];

    /**
     * Returns whether a variable is set.
     *
     * @param string $offset
     * @return bool
     */
    public function __isset(string $offset): bool
    {
        return isset($this->_template_variables[$offset]);
    }

    /**
     * Stores a variable.
     *
     * @param string $offset
     * @param mixed $value
     */
    public function __set(string $offset, $value): void
    {
        $this->_template_variables[$offset] = $value;
    }

    /**
     * Returns a previously set variable.
     *
     * @param string $offset
     * @return mixed
     */
    public function &__get(string $offset)
    {
        if (!isset($this->_template_variables[$offset])) {
            $this->_template_variables[$offset] = null;
        }
        return $this->_template_variables[$offset];
    }

    /**
     * Unsets a previously set variable
     *
     * @param string $offset
     */
    public function __unset(string $offset): void
    {
        unset($this->_template_variables[$offset]);
    }

    public function get_assigned_variables(): array
    {
        $variables = $this->_template_variables;
        $variables['controller'] = $this;
        return $variables;
    }
}
