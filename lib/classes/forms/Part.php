<?php

namespace Studip\Forms;

abstract class Part
{
    protected $parent = null;
    protected $contextobject = null;
    protected $parts = [];
    public $if = null;

    /**
     * Constructor of this Part. Can take one or more Part objects or Input objects or arrays representing an Input object.
     * @param ...$parts
     */
    public function __construct(...$parts)
    {
        foreach ($parts as $part) {
            if (is_subclass_of($part, Part::class)) {
                $this->addPart($part);
            } else {
                if (!is_array($part)) {
                    $part->setParent($this);
                }
                $this->parts[] = $part;
            }
        }
    }

    /**
     * Sets the context-object which is most likely a SimpleORMap object
     * @param $object
     * @return $this
     */
    public function setContextObject($object)
    {
        $this->contextobject = $object;
        return $this;
    }

    /**
     * Returns the context object of this Part if there is any. If there is none it tries to return the context-object
     * of a parent object.
     * @return void|null
     */
    public function getContextObject()
    {
        if ($this->contextobject) {
            return $this->contextobject;
        } elseif ($this->parent) {
            return $this->parent->getContextObject();
        }
    }

    /**
     * Adds a Part object on the next layer.
     * @param Part $part
     * @return $this
     */
    public function addPart(Part $part)
    {
        $part->setParent($this);
        $this->parts[] = $part;
        return $this;
    }

    /**
     * Adds an Input to this Part.
     * @param Input $input
     * @return $this
     */
    public function addInput(Input $input)
    {
        $input->setParent($this);
        $this->parts[] = $input;
        return $this;
    }

    /**
     * Adds a text block inside the form.
     *
     * @param string $text The text to be added.
     * @param bool $text_is_html Whether the text is HTML (true) or plain text (false). Defaults to true.
     * @return $this
     */
    public function addText(string $text, bool $text_is_html = true)
    {
        $text_part = new Text();
        $text_part->setText($text, $text_is_html);
        $text_part->setParent($this);
        $this->parts[] = $text_part;
        return $this;
    }

    /**
     * Adds a link as a form part.
     *
     * @param string $title The title of the link.
     * @param string $url The URL of the link.
     * @param \Icon|null $icon The icon to be used for the link.
     * @param array $attributes Additional link attributes.
     *
     * @return $this
     */
    public function addLink(string $title, string $url, ?\Icon $icon = null, array $attributes = [])
    {
        $link = new Link($url, $title, $icon);
        $link->setAttributes($attributes);

        $this->addPart($link);

        return $this;
    }

    /**
     * Renders this Part object. This could be a section or any other HTML element with child-elements.
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * Renders the Part element with a condition.
     * @return string
     */
    public function renderWithCondition()
    {
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
     * Recursively returns all Input elements attached to this Part object or any child Parts.
     * @return array
     */
    public function getAllInputs()
    {
        $inputs = [];
        foreach ($this->parts as $part) {
            if (is_subclass_of($part, Input::class) && $part->permission) {
                $inputs[] = $part;
            } elseif(is_subclass_of($part, Part::class)) {
                $inputs = array_merge($inputs, $part->getAllInputs());
            }
        }
        return $inputs;
    }

    /**
     * Sets the parent object of this Part. Usually this is done automatically.
     * @param Part $parent
     * @return $this
     * @throws \Exception
     */
    public function setParent(Part $parent)
    {
        $this->parent = $parent;
        //Inputs aktualisieren?
        foreach ($this->parts as $i => $part) {
            if (is_array($part)) {
                $input = $this->getInputFromArray($part);
                $input->setParent($this);
                $this->parts[$i] = $input;
            }
        }
        return $this;
    }

    /**
     * Sets a condition to display this Part. The condition is a javascript condition which is used by vue to
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
     * Returns an Input element from an array.
     * @param array $data
     * @return array|mixed
     * @throws \Exception
     */
    public function getInputFromArray(array $data)
    {
        // Normalize data
        $data = array_merge([
            'label'      => $data['name'] ?? null,
            'value'      => null,
            'attributes' => [],
        ], $data);

        $context = $this->getContextObject();
        if ($context && method_exists($context, 'getTableMetadata')) {
            $metadata = $context->getTableMetadata();
            $meta = $metadata['fields'][$data['name']] ?? null;
            if (!isset($data['type'])) {
                if ($meta) {
                    $data = array_merge(Input::getFielddataFromMeta($meta, $context), $data);
                } else {
                    $data['type'] = 'text';
                }
            }
        }
        if (!isset($data['label'])) {
            $data['label'] = $data['name'];
        }

        if (!isset($data['value']) && $context && method_exists($context, 'isField')) {
            if ($context->isField($data['name'])) {
                $data['value'] = $context[$data['name']];
            }
        }
        if (!$data['type']) {
            return $data;
        }

        $classname = "\\Studip\\Forms\\".ucfirst($data['type'])."Input";
        $attributes = $data;
        unset(
            $attributes['name'],
            $attributes['label'],
            $attributes['value'],
            $attributes['type'],
            $attributes['mapper'],
            $attributes['store'],
            $attributes['if'],
            $attributes['permission'],
            $attributes['required'],
            $attributes['attributes']
        );
        $attributes = array_merge($attributes, (array) $data['attributes']);
        if (class_exists($classname)) {
            $input = new $classname($data['name'], $data['label'], $data['value'], $attributes);
        } elseif (class_exists($data['type'])) {
            $classname = $data['type'];
            $input = new $classname($data['name'], $data['label'], $data['value'], $attributes);
        } else {
            //this should not happen:
            throw new \Exception(sprintf(_("Klasse %s oder %s existiert nicht."), $classname, $data['type']));
        }

        if (isset($data['mapper']) && is_callable($data['mapper'])) {
            $input->mapper = $data['mapper'];
        }
        if (isset($data['store']) && is_callable($data['store'])) {
            $input->store = $data['store'];
        }
        if (!empty($data['if'])) {
            $input->if = $data['if'];
        }
        if (isset($data['permission'])) {
            $input->permission = $data['permission'];
        }

        $input->required = !empty($data['required']);

        return $input;
    }
}
