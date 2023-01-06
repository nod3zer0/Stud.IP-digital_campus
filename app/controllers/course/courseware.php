<?php

require_once __DIR__.'/../courseware_controller.php';

use Courseware\StructuralElement;
use Courseware\Unit;

/**
 * @property ?string $entry_element_id
 * @property int $last_visitdate
 * @property mixed $course_id
 * @property mixed $courseware_progress_data
 * @property mixed $courseware_chapter_counter
 */
class Course_CoursewareController extends CoursewareController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args): void
    {
        parent::before_filter($action, $args);

        PageLayout::setTitle(_('Courseware'));
        PageLayout::setHelpKeyword('Basis.Courseware');

        checkObject();
        if (!Context::isCourse()) {
            throw new CheckObjectException(_('Es wurde keine passende Veranstaltung gefunden.'));
        }
        $this->studip_module = checkObjectModule('CoursewareModule', true);
        object_set_visit_module($this->studip_module->getPluginId());
        $this->last_visitdate = object_get_visit(Context::getId(), $this->studip_module->getPluginId());
    }

    public function index_action(): void
    {
        Navigation::activateItem('course/courseware/shelf');
        $this->licenses = $this->getLicenses();
        $this->setIndexSidebar();
    }

    public function courseware_action($unit_id = null):  void
    {
        global $perm, $user;

        $this->user_id = $user->id;
        /** @var array<mixed> $last */
        $last = UserConfig::get($this->user_id)->getValue('COURSEWARE_LAST_ELEMENT');

        if ($unit_id === null) {
            $this->redirectToFirstUnit('course', Context::getId(), $last);

            return;
        }

        $this->entry_element_id = null;
        $this->unit_id = null;
        $unit = Unit::find($unit_id);
        if (isset($unit)) {
            $this->setEntryElement('course', $unit, $last, Context::getId());

            Navigation::activateItem('course/courseware/unit');
            $this->licenses = $this->getLicenses();
            $this->setCoursewareSidebar();
        }
    }

    public function tasks_action(): void
    {
        global $perm, $user;
        $this->is_teacher = $perm->have_studip_perm('tutor', Context::getId(), $user->id);
        Navigation::activateItem('course/courseware/tasks');
        $this->setTasksSidebar();
    }

    public function activities_action(): void
    {
        global $perm, $user;
        $this->is_teacher = $perm->have_studip_perm('tutor', Context::getId(), $user->id);
        Navigation::activateItem('course/courseware/activities');
        $this->setActivitiesSidebar();
    }

    public function pdf_export_action($element_id, $with_children): void
    {
        $element = \Courseware\StructuralElement::findOneById($element_id);
        $user = User::find($GLOBALS['user']->id);
        $this->render_pdf($element->pdfExport($user, $with_children), trim($element->title).'.pdf');
    }

    private function setIndexSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
        $sidebar->addWidget(new VueWidget('courseware-import-widget'));
    }

    private function setTasksSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
    }

    private function setActivitiesSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-activities-widget-filter-type'));
        $sidebar->addWidget(new VueWidget('courseware-activities-widget-filter-unit'));
    }
}
