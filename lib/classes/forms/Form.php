<?php

namespace Studip\Forms;

class Form extends Part
{

    //models:
    protected $store_callbacks = [];

    //internals
    protected $inputs = [];
    protected $parts = [];
    protected $buttons = [];

    //appearance in html-form
    protected $url = null;
    protected $save_button_text = '';
    protected $save_button_name = 'STUDIPFORM_STORE_BUTTON';

    protected $cancel_button_text = '';
    protected $cancel_button_name = '';
    protected $autoStore = false;
    protected $debugmode = false;
    protected $success_message = '';

    protected $collapsable = false;
    protected $data_secure = true;

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
        parent::__construct(...$parts);
        //Set a default for the success message:
        $this->success_message = _('Daten wurden gespeichert.');
        \NotificationCenter::addObserver($this, 'validationStep', 'ActionDidPerform');
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

        // Normalize parameters
        $params = array_merge([
            'types'   => [],
            'fields'  => [],
            'without' => [],
        ], $params);

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
                    $meta = $metadata['fields'][$fieldname] ?? null;
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

    /**
     * Sets the text for the "save" button in the form.
     *
     * @param string $text The text for the button to save the form.
     * @return $this
     */
    public function setSaveButtonText(string $text): Form
    {
        $this->save_button_text = $text;
        return $this;
    }

    /**
     * @return string The text for the "save" button in the form.
     */
    public function getSaveButtonText() : string
    {
        return $this->save_button_text ?: _('Speichern');
    }

    public function setSaveButtonName(string $name): Form
    {
        $this->save_button_name = $name;
        return $this;
    }

    public function getSaveButtonName() : string
    {
        return $this->save_button_name ?: $this->getSaveButtonText();
    }

    public function setCancelButtonText(string $text): Form
    {
        $this->cancel_button_text = $text;
        return $this;
    }

    /**
     * @return string The text for the "save" button in the form.
     */
    public function getCancelButtonText() : string
    {
        return $this->cancel_button_text ?: _('Abbrechen');
    }

    public function setCancelButtonName(string $name): Form
    {
        $this->cancel_button_name = $name;
        return $this;
    }

    public function getCancelButtonName() : string
    {
        return $this->cancel_button_name ?: $this->getCancelButtonText();
    }

    public function setSuccessMessage(string $success_message): Form
    {
        $this->success_message = $success_message;
        return $this;
    }

    public function setDebugMode(bool $debug = true): Form
    {
        $this->debugmode = $debug;
        return $this;
    }

    public function getDebugMode(): bool
    {
        return $this->debugmode;
    }

    public function getSuccessMessage() : string
    {
        return $this->success_message;
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
        if (
             \Request::isPost()
             && \Request::isAjax()
             && !\Request::isDialog()
             && \Request::submitted('STUDIPFORM_AUTOSTORE')
        ) {
            if (\Request::submitted('STUDIPFORM_SERVERVALIDATION')) {
                $this->validate();
            } else {
                //storing the input
                $this->store();
                if ($this->success_message) {
                    \PageLayout::postSuccess($this->success_message);
                }
                page_close();
                die();
            }
        }
        return $this;
    }

    public function validate()
    {
        if (\Request::isPost() && \Request::submitted('STUDIPFORM_SERVERVALIDATION')) {
            //verify the user input:
            $output = [];
            foreach ($this->getAllInputs() as $input) {
                if ($input->validate) {
                    $callback = $input->getValidationCallback();
                    $value = $this->getStorableValueFromRequest($input);
                    $valid = $callback($value, $input);
                    if ($valid !== true) {
                        $output[$input->getName()] = [
                            'name' => $input->getName(),
                            'label' => $input->getTitle(),
                            'error' => $callback($value, $input)
                        ];
                    }
                }
            }
            header('Content-Type: application/json');
            echo json_encode($output);
            page_close();
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
    public function addStoreCallback(Callable $c): Form
    {
        $this->store_callbacks[] = $c;
        return $this;
    }

    /**
     * Sets if the form should be secured against accidental leaving of the page. Standard is on.
     * @param $data_secure
     * @return $this
     */
    public function setDataSecure($data_secure)
    {
        $this->data_secure = $data_secure;
        return $this;
    }

    public function getDataSecure() {
        return $this->data_secure;
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

        foreach ($this->getAllInputs() as $input) {
            if ($input->validate) {
                $callback = $input->getValidationCallback();
                $value = $this->getStorableValueFromRequest($input);
                $valid = $callback($value, $input);
                if ($valid !== true) {
                    return $stored;
                }
            }
        }

        //store by each input
        $all_values = [];
        foreach ($this->getAllInputs() as $input) {
            $value = $this->getStorableValueFromRequest($input);
            $callback = $this->getStoringCallback($input);
            if (is_callable($callback)) {
                $stored += $callback($value, $input);
            }
            $all_values[$input->getName()] = $value;
        }

        foreach ($this->parts as $part) {
            $context = $part->getContextObject();
            if ($context && method_exists($context, 'store')) {
                $stored += $context->store();
            }
        }

        foreach ($this->store_callbacks as $callback) {
            if (is_callable($callback)) {
                $stored += call_user_func($callback, $this, $all_values);
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
     * Adds a Studip-Button object to the footer of the dialog.
     * @param \Studip\Button $button
     * @return Form
     */
    public function addButton(\Studip\Button $button) : Form
    {
        $this->buttons[] = $button;
        return $this;
    }

    /**
     * Returns the additional buttons (except the save-button) as an array of \Studip\Button objects
     * @return array
     */
    public function getButtons() : array
    {
        return $this->buttons;
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
        if (
            $context
            && is_subclass_of($context, \SimpleORMap::class)
            && ($context->isField($input->getName()) || $context->isRelation($input->getName()))
        ) {
            return function ($value) use ($context, $input) {
                if ($context && !$value && $value !== null) {
                    $metadata = $context->getTableMetadata();
                    if ($metadata['fields'][$input->getName()]['null'] === 'YES') {
                        //sets the value to null if this is a feasible db value for this field:
                        $value = null;
                    }
                }
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
