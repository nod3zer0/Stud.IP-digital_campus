<?php
/**
 * This widget type delegates all rendering of the widget to vuejs.
 *
 * @author  Elmar Ludwig
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */
class VueWidget extends Widget
{
    /**
     * Constructs the widget with the given id on the element.
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->layout = 'widgets/vue-widget';
        $this->forced_rendering = true;
    }
}
