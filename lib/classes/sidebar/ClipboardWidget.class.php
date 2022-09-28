<?php

/**
 * This class is responsible for displaying new clipboards
 * (the ones that use the Clipboard SORM class) in the sidebar.
 *
 * @author  Moritz Strohm <strohm@data-quest.de>
 * @license GNU General Public License v2 or later.
 * @since   4.5
 */
class ClipboardWidget extends SidebarWidget
{
    protected $draggable_items;

    /**
     * clipboard_widget_id is required in the case that multiple
     * clipboard widgets exist on one page. The JavaScript code
     * can then distinguish each clipboard widget by its unique ID.
     */
    protected $clipboard_widget_id;


    /**
     * This attribute holds the ID of the clipboard which is stored in the
     * session as currently selected clipboard.
     */
    protected $current_clipboard_id;


    /**
     * This attribute contains a string that shall be the title of the button
     * for applying the selected clipbard to the main area
     * the widget is showing when in read only mode.
     */
    protected $apply_button_title;

    /**
     * This widget can be initialised with the class names of allowed classes
     * to limit the displayed items in a clipboard to items of specific
     * classes.
     */
    public function __construct($allowed_item_classes = [])
    {
        parent::__construct();

        if ($allowed_item_classes) {
            //Check if all allowed item classes are SimpleORMap objects
            //and if the classes implement the StudipItem interface:
            foreach ($allowed_item_classes as $class) {
                if (!is_subclass_of($class, 'StudipItem')) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'The class %s does not implement the StudipItem interface which is required for clipboard items!',
                            htmlReady($class)
                        )
                    );
                }
            }
        } else {
            //Allow all StudipItem implementations:
            $allowed_item_classes = ['StudipItem'];
        }

        $this->allowed_item_classes = $allowed_item_classes;
        $this->template = 'sidebar/clipboard-widget';
        $this->title = _('Eigene Merkzettel');
        $this->apply_button_title = _('Hauptbereich aktualisieren');

        $this->clipboard_widget_id = md5(uniqid('clipboard_widget_id'));

        $this->updateSessionVariables();
        $this->current_clipboard_id = $_SESSION['selected_clipboard_id'] ?? '';

        $this->setId("ClipboardWidget_{$this->clipboard_widget_id}");
        $this->setAdditionalAttribute('data-widget_id', $this->clipboard_widget_id);
        $this->addLayoutCSSClass('clipboard-widget');
    }

    /**
     * Updates session variables if a special POST request is made.
     */
    public function updateSessionVariables()
    {
        if (Request::submitted('clipboard_update_session_special_action')) {
            CSRFProtection::verifyUnsafeRequest();

            $_SESSION['selected_clipboard_id'] = Request::get('selected_clipboard_id');
        }
    }

    public function setApplyButtonTitle($title = '')
    {
        if ($title) {
            $this->apply_button_title = $title;
        }
    }

    public function getClipboardWidgetId()
    {
        return $this->clipboard_widget_id;
    }

    public function render($variables = [])
    {
        $clipboards = Clipboard::getClipboardsForUser(
            $GLOBALS['user']->id
        );

        if (!$this->current_clipboard_id && $clipboards) {
            $_SESSION['selected_clipboard_id'] = $clipboards[0]->id;
            $this->current_clipboard_id = $clipboards[0]->id;
        }

        return parent::render($variables + [
            'clipboards'               => $clipboards,
            'allowed_item_classes'     => $this->allowed_item_classes,
            'clipboard_widget_id'      => $this->clipboard_widget_id,
            'draggable_items'          => $this->draggable_items,
            'apply_button_title'       => $this->apply_button_title,
            'elements'                 => $this->elements,
            'selected_clipboard_id'    => $this->current_clipboard_id,
        ]);
    }

    /**
     * Adds a link to the widget
     *
     * @param String $label  Label/content of the link
     * @param String $url    URL/Location of the link
     * @param Icon   $icon   instance of class Icon for the link
     * @param bool   $active Pass true if the link is currently active,
     *                       defaults to false
     */
    public function &addLink($label, $url, $icon = null, $attributes = array(), $index = null)
    {
        if ($index === null) {
            $index = 'link-' . md5($url);
        }
        $element = new LinkElement($label, $url, $icon, $attributes);
        $this->addElement($element, $index);
        return $element;
    }
}
