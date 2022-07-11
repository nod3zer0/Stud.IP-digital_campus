<?php

namespace Studip\Forms;

class Form extends Part
{

    //models:
    protected $afterStore = [];

    //internals
    protected $inputs = [];
    protected $parts = [];

    //appearance in html-form
    protected $url = null;
    protected $autoStore = false;
    protected $collapsable = false;

    //to identify a form element
    protected $id = null;

    /**
     * Creates a new Form object from a SORM object so that each field of the db-table becomes
     * an input-field of the form. You can modify the form by the params.
     * @param \SimpleORMap $object
     * @param array $params
     * @param string|null $url
     * @return Form
     */
    public static function fromSORM(\SimpleORMap $object, $params = [], $url = null)
    {
        $form = static::create();
        $form->addSORM($object, $params);
        if ($url) {
            $form->setURL($url);
        }
        return $form;
    }


    /**
     * A static constructor for an empty Form object.
     * @return Form
     */
    public static function create() : Form
    {
        $form = new static();
        return $form;
    }

    /**
     * Finalized constructor.
     *
     * @param mixed[] ...$parts
     */
    final public function __construct(...$parts)
    {
        parent::__construct($parts);
    }

    /**
     * Adds a new Fieldset to the Form object with the SORM object's fields as
     * input fields. These fields can be modified or specified by the $params array.
     * @param \SimpleORMap $object
     * @param array $params
     * @return Form $this
     */
    public function addSORM(\SimpleORMap $object, array $params = [])
    {
        $metadata = $object->getTableMetadata();

        if ($params['fields']) {
            //Setting the label
            foreach ($params['fields'] as $fieldname => $fielddata) {
                if (is_string($fielddata)) {
                    $params['fields'][$fieldname] = [
                        'label' => $fielddata
                    ];
                }
            }
            //Setting the type and name
            foreach ($params['fields'] as $fieldname => $fielddata) {
                if (is_array($fielddata)) {
                    $meta = $metadata['fields'][$fieldname];
                    if (!isset($fielddata['type'])) {
                        if ($meta) {
                            $fielddata = array_merge(Input::getFielddataFromMeta($meta, $object), $fielddata);
                        } else {
                            $fielddata['type'] = 'text';
                        }

                        $params['fields'][$fieldname] = $fielddata;
                    }
                    $params['fields'][$fieldname]['name'] = $fieldname;
                }
            }
        } else {
            foreach ($metadata['fields'] as $attribute => $meta) {
                if (!in_array($attribute, (array) $params['without'])) {
                    $fielddata = [
                        'label' => $attribute
                    ];
                    $fielddata = array_merge(Input::getFielddataFromMeta($meta, $object), $fielddata);

                    $params['fields'][$attribute] = $fielddata;
                }
            }
        }
        foreach ($params['fields'] as $fieldname => $fielddata) {
            if (is_array($fielddata) && !array_key_exists('value', $fielddata)) {
                if ($object->isField($fieldname)) {
                    $params['fields'][$fieldname]['value'] = $object[$fieldname];
                }
            }
        }
        foreach ((array) $params['types'] as $fieldname => $type) {
            $params['fields'][$fieldname]['type'] = $type;
        }
        //respect the without param:
        foreach ((array) $params['without'] as $fieldname) {
            unset($params['fields'][$fieldname]);
        }
        $fields = $params['fields'];

        //Now initializing the fieldset:
        $fieldset = new Fieldset($params['legend'] ?: _("Daten"));
        $fieldset->setContextObject($object);
        $this->addPart($fieldset);

        foreach ($fields as $fieldname => $fielddata) {
            if (is_array($fielddata)) {
                $fieldset->addInput($fieldset->getInputFromArray($fielddata));
            } elseif(is_subclass_of($fielddata, Part::class)) {
                $fieldset->addPart($fielddata);
            } elseif(is_subclass_of($fielddata, Input::class)) {
                $fieldset->addInput($fielddata);
            }
        }
        return $this;
    }

    /**
     * Sets the URL where the Form should be leading after submitting.
     * @param $url
     * @return Form $this
     */
    public function setURL($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the URL where the Form is leading to after the submit.
     * @return string|null
     */
    public function getURL()
    {
        return $this->url;
    }

    public function setCollapsable($collapsing = true)
    {
        $this->collapsable = $collapsing;
        return $this;
    }

    public function isCollapsable()
    {
        return $this->collapsable;
    }

    /**
     * Stores the Form object if this is a POST-request. This also erases the URL so that the auto-save URL
     * will be set automatically to the current $_SERVER['REQUEST_URI'].
     * @return $this
     * @throws \AccessDeniedException
     */
    public function autoStore()
    {
        $this->autoStore = true;
        if (\Request::isPost() && \Request::isAjax() && !\Request::isDialog()) {
            $this->store();
            \PageLayout::postSuccess(_('Daten wurden gespeichert.'));
            die();
        }
        return $this;
    }

    public function isAutoStoring()
    {
        return $this->autoStore;
    }

    /**
     * Adds a callback function that is executed right after the store-method. That callback receives this
     * Form object as the only parameter.
     * @param callable $c
     * @return Form $this
     */
    public function addAfterStoreCallback(Callable $c)
    {
        $this->afterStore[] = $c;
        return $this;
    }

    /**
     * Sets the ID if this form. This ID is only relevant for plugins to identify this Form object.
     * @param string|null $id
     * @return Form $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Returns the ID if this form. This ID is only relevant for plugins to identify this Form object.
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the number of storing processes
     * @return: a number of storing processes. 0 if nothing was stored.
     */
    public function store()
    {
        if (!\CSRFProtection::verifyRequest()) {
            throw new \AccessDeniedException();
        }
        \NotificationCenter::postNotification('FormWillStore', $this);

        $stored = 0;

        //store by each input
        foreach ($this->getAllInputs() as $input) {
            $value = $this->getStorableValueFromRequest($input);
            if ($value !== null) {
                $callback = $this->getStoringCallback($input);
                $stored += $callback($value, $input);
            }
        }

        foreach ($this->parts as $part) {
            $context = $part->getContextObject();
            if ($context && method_exists($context, 'store')) {
                $stored += $context->store();
            }
        }

        foreach ($this->afterStore as $callback) {
            if (is_callable($callback)) {
                $stored += call_user_func($callback, $this);
            } else {
                //throw warning if callback is not available:
                if ($callback === null) {
                    $callback = 'NULL';
                }
                trigger_error(sprintf('Could not execute callback %s in Form object.', $callback), E_USER_WARNING);
            }
        }
        return $stored;
    }

    /**
     * Adds a Part object to this form like a fieldset
     * @param Part $part
     * @return Form|void
     */
    public function addPart(Part $part)
    {
        $part->setParent($this);
        $this->parts[] = $part;
    }

    /**
     * Returns all the Part objects like Fieldsets as an array.
     * @return array
     */
    public function getParts() : array
    {
        return $this->parts;
    }

    /**
     * Returns the last part of the form. If there is none yet, it will create a fieldset and return that.
     * @return Part
     */
    public function getLastPart() : Part
    {
        if (count($this->parts) === 0) {
            $this->parts[] = new Fieldset();
        }
        return $this->parts[count($this->parts) - 1];
    }

    /**
     * Renders the whole form as a string.
     * @return string
     * @throws \Flexi_TemplateNotFoundException
     */
    public function render()
    {
        \NotificationCenter::postNotification('FormWillRender', $this);
        $template = $GLOBALS['template_factory']->open('forms/form');
        $template->form = $this;
        return $template->render();
    }

    /**
     * Returns the function to be used to store the value into the input. If the given Input has no storing
     * function it will generate a Closuer to set the value to the SimpleORMap context object.
     * @param $input
     * @return \Closure|void
     */
    protected function getStoringCallback(Input $input)
    {
        if ($input->store) {
            return $input->store;
        }
        $context = $input->getParent()->getContextObject();
        if ($context && is_subclass_of($context, \SimpleORMap::class)) {
            return function ($value) use ($context, $input) {
                $context[$input->getName()] = $value;
            };
        }
    }

    /**
     * Returns the value for the Input object from the $_REQUEST. This value will also be mapped by
     * the Input's dataMapper function and after that by a special mapper-callback the Input
     * probably has.
     * @param Input $input
     * @return mixed
     */
    protected function getStorableValueFromRequest(Input $input)
    {
        $requestparam = $input->getName();
        $bracket_pos = strpos($requestparam, "[");
        if ($bracket_pos !== false) {
            $requestparam = substr($requestparam, 0, $bracket_pos);
            $value = \Request::getArray($requestparam);
            foreach ($value as $i => $v) {
                $value[$i] = $input->dataMapper($v);
            }
        } else {
            $value = $input->getRequestValue();
            $value = $input->dataMapper($value);
        }
        if ($input->mapper && is_callable($input->mapper)) {
            $mapper = $input->mapper;
            $value = $mapper($value, $input->getContextObject());
        }
        return $value;
    }
}
