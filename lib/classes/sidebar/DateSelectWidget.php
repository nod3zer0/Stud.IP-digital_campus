<?php

class DateSelectWidget extends SidebarWidget
{
    protected $date = null;
    protected $calendar_control = false;

    public function __construct()
    {
        $this->template = 'sidebar/date-select-widget';
        $this->date = new DateTime();
        parent::__construct();
    }

    public function setCalendarControl(bool $calendar_control = false) : void
    {
        $this->calendar_control = $calendar_control;
    }

    public function setDate(DateTime $date) : void
    {
        $this->date = $date;
    }

    public function getDate() : ?DateTime
    {
        return $this->date;
    }

    public function getCalendarControlStatus() : bool
    {
        return $this->calendar_control;
    }

    public function render($variables = []) : string
    {
        $template = $GLOBALS['template_factory']->open($this->template);
        $template->set_attributes($variables + $this->template_variables);
        $template->set_attribute('title', _('Datum auswählen'));
        $template->set_attribute('date', $this->date);
        $template->set_attribute('calendar_control', $this->calendar_control);

        if ($this->layout) {
            $layout = $GLOBALS['template_factory']->open($this->layout);
            $layout->layout_css_classes = $this->layout_css_classes;
            $template->set_layout($layout);
        }

        return $template->render();
    }
}
