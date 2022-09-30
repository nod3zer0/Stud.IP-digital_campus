<?php

class CourseManagementSelectWidget extends SelectWidget
{
    public $course = null;
    public $order_by_field;

    public function __construct()
    {
        parent::__construct(_('Veranstaltungen'), '?#admin_top_links', 'cid');

        $this->course = Course::findCurrent();
        $this->order_by_field = UserConfig::get($GLOBALS['user']->id)->COURSE_MANAGEMENT_SELECTOR_ORDER_BY ?? 'name';
    }

    public function render($variables = [])
    {
        $extra = sprintf(
            '<a href="%s" title="%s" data-dialog="size=auto">%s</a>',
            URLHelper::getURL('dispatch.php/course/management/order_settings', ['cid' => $this->course->id, 'from' => Request::url()]),
            _('Sortiereinstellungen'),
            Icon::create('settings')
        );
        $this->setExtra($extra);
        $this->class = 'nested-select';
        $this->setDropdownAutoWidth(true);
        $seminars = AdminCourseFilter::get()->getCoursesForAdminWidget($this->order_by_field);
        foreach ($seminars as $seminar) {
            if ($this->order_by_field === 'number') {
                $seminar_name = trim($seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name']);
            } else {
                $seminar_name = $seminar['Name'];

                if ($seminar['VeranstaltungsNummer']) {
                    $seminar_name .= sprintf(' (%s)', trim($seminar['VeranstaltungsNummer']));
                }
            }
            $this->addElement(new SelectElement(
                $seminar['Seminar_id'],
                $seminar_name,
                $seminar['Seminar_id'] === $this->course->id,
                trim($seminar['VeranstaltungsNummer'] . ' ' . $seminar['Name'])
            ));
        }
        return parent::render($variables);
    }
}
