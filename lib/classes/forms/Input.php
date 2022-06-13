<?php

namespace Studip\Forms;

abstract class Input
{
    protected $title = null;
    protected $value = null;
    protected $attributes = [];
    protected $name = null;
    protected $parent = null;
    public $mapper = null;
    public $store = null;
    public $if = null;
    public $permission = true;
    public $required = false;

    /**
     * A static constructor. Returns a new Input-object.
     * @param string $name
     * @param string $title
     * @param mixed $value
     * @param array $attributes
     * @return static
     */
    public static function create($name, $title, $value, array $attributes = [])
    {
        return new static($name, $title, $value, $attributes);
    }

    /**
     * This static method returns fielddata as an array from metadata of a database-field.
     * This array will be used internally to create the best fitting Input object to the
     * database field.
     * @param array $meta
     * @param $object: most likely a SimpleORMap object.
     * @return array
     */
    public static function getFielddataFromMeta($meta, $object) : array
    {
        $bracket_pos = strpos($meta['type'], '(');
        if ($bracket_pos !== false) {
            $type = substr($meta['type'], 0, $bracket_pos);
        } else {
            $type = $meta['type'];
        }
        $fielddata = [];
        switch ($type) {
            case 'enum':
                $fielddata['type'] = 'select';
                preg_match("/\((.*)\)/", $meta['type'], $matches);
                $matches = explode(',', $matches[1]);
                foreach ($matches as $key => $status) {
                    $matches[$key] = substr($status, 1, strlen($status) - 2);
                }
                $fielddata['attributes']['options'] = $matches;
                break;
            case 'tinyint':
                preg_match("/\((.*)\)/", $meta['type'], $matches);
                if ($matches[1] == 1) {
                    $fielddata['type'] = 'checkbox';
                    break;
                }
            case 'integer':
                $fielddata['type'] = 'number';
                break;
            case 'text':
                if ($object->isI18nField($meta['name'])) {
                    $fielddata['type'] = 'i18n_textarea';
                } else {
                    $fielddata['type'] = 'textarea';
                }
                break;
            default:
                if ($object->isI18nField($meta['name'])) {
                    $fielddata['type'] = 'i18n_text';
                } else {
                    $fielddata['type'] = 'text';
                }
        }
        return $fielddata;
    }

    /**
     * Constructor of the Input class.
     * @param $name
     * @param $title
     * @param $value
     * @param $attributes
     */
    public function __construct($name, $title, $value, array $attributes = [])
    {
        $this->name = $name;
        $this->title = $title;
        $this->value = $value;
        $this->attributes = $attributes;
    }

    /**
     * Sets the parent of this Input object. Usually this is done automatically by the framework in the moment that
     * the input is initialized in the Form object. So you usually don't need to call this method on your own.
     * @param Part $parent
     * @return $this
     */
    public function setParent(Part $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Returns the parent of this Input if there is already one.
     * @return null|Part
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function dataMapper($value)
    {
        return $value;
    }

    /**
     * Returns the name of the given input. Also have a look at the method getAllInputNames if you want to
     * provide multiple input elements (like in i18n input fields) within one virtual input. In that case
     * this getName method returns the main-input name like the attribute in the SORM class.
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of this input.
     * @return null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns an array with all names of all inputs that this Input-object has. Normally this is just one
     * name because there is only one input. But if you think of i18n-inputs there are possibly more
     * textareas - one for each language. In that case this function would return all names of all inputs that
     * are present.
     * @return string[]
     */
    public function getAllInputNames()
    {
        return [$this->getName()];
    }

    /**
     * Renders the Input but maybe encapsulated in a template that is displayed only if a condition is true.
     * This is helpful for the if-attribute on the Input like in setIfCondition.
     * @return string
     */
    public function renderWithCondition()
    {
        if (!$this->permission) {
            return '';
        }
        $html = $this->render();
        if (!trim($html)) {
            return '';
        }
        if ($this->if !== null) {
            $html = '<template v-if="' . htmlReady($this->if) . '">' . $html . '</template>';
        }
        return $html;
    }

    /**
     * This renders the Input.
     * @return string
     */
    abstract public function render();

    /**
     * Returns the context-object which is usually a SimpleORMap object.
     * @return null|\SimpleORMap
     */
    public function getContextObject()
    {
        if ($this->getParent()) {
            return $this->getParent()->getContextObject();
        }
        return null;
    }

    /**
     * Sets a special mapper-function to turn the request-values into the real values for the database.
     * @param callable $callback
     * @return $this
     */
    public function setMapper(Callable $callback)
    {
        $this->mapper = $callback;
        return $this;
    }

    /**
     * Sets the storing function. This would override the normal storing-function that just sets the value
     * of a given context object like a SORM object.
     * @param callable $store
     * @return $this
     */
    public function setStoringFunction(Callable $store)
    {
        $this->store = $store;
        return $this;
    }

    /**
     * Sets a condition to display this input. The condition is a javascript condition which is used by vue to
     * hide the input if the condition is not satisfies.
     * @param string $if
     * @return $this
     */
    public function setIfCondition($if)
    {
        $this->if = $if;
        return $this;
    }

    /**
     * Set if the user is able to see and edit this input
     * @param boolean $if
     * @return $this
     */
    public function setPermission(bool $permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Marks the input as a required field.
     * @param $required
     * @return $this
     */
    public function setRequired($required = true)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Returns the values from the request. Normally this is \Request::get, but special Input-classes could also
     * return arrays or objects.
     * @return string|null
     */
    public function getRequestValue()
    {
        return \Request::get($this->name);
    }

    protected function extractOptionsFromAttributes(array &$attributes)
    {
        $options = null;
        if (isset($attributes['options'])) {
            $options = $attributes['options'];
            unset($attributes['options']);
        }
        return $options;
    }
}
