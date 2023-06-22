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

        PageLayout::setTitle(Context::get()->getFullname() . ' - ' . _('Courseware'));
        PageLayout::setHelpKeyword('Basis.Courseware');

        checkObject();
        if (!Context::isCourse()) {
            throw new CheckObjectException(_('Es wurde keine passende Veranstaltung gefunden.'));
        }
        $this->studip_module = checkObjectModule('CoursewareModule', true);
        object_set_visit_module($this->studip_module->getPluginId());
        $this->last_visitdate = object_get_visit(Context::getId(), $this->studip_module->getPluginId());
        $this->licenses = $this->getLicenses();
        $this->unitsNotFound = Unit::countBySql('range_id = ?', [Context::getId()]) === 0;
    }

    public function index_action(): void
    {
        Navigation::activateItem('course/courseware/shelf');
        $this->setIndexSidebar();
    }

    public function courseware_action($unit_id = null):  void
    {
        global $perm, $user;
        Navigation::activateItem('course/courseware/unit');
        if ($this->unitsNotFound) {
            PageLayout::postMessage(MessageBox::info(_('Es wurde kein Lernmaterial gefunden.')));
            return;
        }
        $this->setCoursewareSidebar();

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

    public function comments_overview_action(): void
    {
        Navigation::activateItem('course/courseware/comments');
        $this->setCommentsOverviewSidebar();
    }

    public function comments_overview_data_action()
    {
        $user = User::findCurrent();
        $cid = Request::get('cid');
        $units = [];
        $elements = [];
        $containers = [];
        $blocks = [];
        $block_comments = [];
        $block_feedbacks = [];
        $element_comments = [];
        $element_feedbacks = [];

        $statement = DBManager::get()->prepare("
            SELECT elem.id AS elem_id, container.id AS container_id, block.id AS block_id, comment.id AS comment_id
            FROM `cw_block_comments` AS comment
            INNER JOIN `cw_blocks` AS block ON (block.id = comment.block_id)
            INNER JOIN `cw_containers` AS container ON (container.id = block.container_id)
            INNER JOIN `cw_structural_elements` AS elem ON (elem.id = container.structural_element_id)
            WHERE elem.range_type = 'course'
            AND elem.range_id = :range_id
        ");

        $statement->execute(['range_id' => $cid]);
        $cw_block_comments = $statement->fetchAll();

        foreach ($cw_block_comments as $row) {
            $element = \Courseware\StructuralElement::find($row['elem_id']);
            if (!$element->canRead($user)) {
                continue;
            }
            if (!$this->arrayHasDataForId($elements, $row['elem_id'])) {
                $elements[] = $element;
                $unit = $element->findUnit();
                $unitElement = $unit->structural_element;
                if (!$this->arrayHasDataForId($elements, $unitElement->id)) {
                    $elements[] = $unitElement;
                }
                if (!$this->arrayHasDataForId($units, $unit->id)) {
                    $units[] = $unit;
                }
            }
            if (!$this->arrayHasDataForId($containers, $row['container_id'])) {
                $containers[] = \Courseware\Container::find($row['container_id']);
            }
            if (!$this->arrayHasDataForId($blocks, $row['block_id'])) {
                $blocks[] = \Courseware\Block::find($row['block_id']);
            }
            if (!$this->arrayHasDataForId($block_comments, $row['comment_id'])) {
                $block_comments[] = \Courseware\BlockComment::find($row['comment_id']);
            }
        }

        $statement = DBManager::get()->prepare("
            SELECT elem.id AS elem_id, container.id AS container_id, block.id AS block_id, feedback.id AS feedback_id
            FROM `cw_block_feedbacks` AS feedback
            INNER JOIN `cw_blocks` AS block ON (block.id = feedback.block_id)
            INNER JOIN `cw_containers` AS container ON (container.id = block.container_id)
            INNER JOIN `cw_structural_elements` AS elem ON (elem.id = container.structural_element_id)
            WHERE elem.range_type = 'course'
            AND elem.range_id = :range_id
        ");

        $statement->execute(['range_id' => $cid]);
        $cw_block_feedbacks = $statement->fetchAll();

        foreach ($cw_block_feedbacks as $row) {
            $element = \Courseware\StructuralElement::find($row['elem_id']);
            if (!$element->canEdit($user)) {
                continue;
            }
            if (!$this->arrayHasDataForId($elements, $row['elem_id'])) {
                $elements[] = $element;
                $unit = $element->findUnit();
                $unitElement = $unit->structural_element;
                if (!$this->arrayHasDataForId($elements, $unitElement->id)) {
                    $elements[] = $unitElement;
                }
                if (!$this->arrayHasDataForId($units, $unit->id)) {
                    $units[] = $unit;
                }
            }
            if (!$this->arrayHasDataForId($containers, $row['container_id'])) {
                $containers[] = \Courseware\Container::find($row['container_id']);
            }
            if (!$this->arrayHasDataForId($blocks, $row['block_id'])) {
                $blocks[] = \Courseware\Block::find($row['block_id']);
            }
            if (!$this->arrayHasDataForId($block_feedbacks, $row['feedback_id'])) {
                $block_feedbacks[] = \Courseware\BlockFeedback::find($row['feedback_id']);
            }
        }

        $statement = DBManager::get()->prepare("
            SELECT elem.id AS elem_id, comment.id AS comment_id
            FROM `cw_structural_element_comments` AS comment
            INNER JOIN `cw_structural_elements` AS elem ON (elem.id = comment.structural_element_id)
            WHERE elem.range_type = 'course'
            AND elem.range_id = :range_id
        ");

        $statement->execute(['range_id' => $cid]);
        $cw_structural_element_comments = $statement->fetchAll();

        foreach ($cw_structural_element_comments as $row) {
            $element = \Courseware\StructuralElement::find($row['elem_id']);
            if (!$element->canRead($user)) {
                continue;
            }
            if (!$this->arrayHasDataForId($elements, $row['elem_id'])) {
                $elements[] = $element;
                $unit = $element->findUnit();
                $unitElement = $unit->structural_element;
                if (!$this->arrayHasDataForId($elements, $unitElement->id)) {
                    $elements[] = $unitElement;
                }
                if (!$this->arrayHasDataForId($units, $unit->id)) {
                    $units[] = $unit;
                }
            }
            if (!$this->arrayHasDataForId($element_comments, $row['comment_id'])) {
                $element_comments[] = \Courseware\StructuralElementComment::find($row['comment_id']);
            }
        }

        $statement = DBManager::get()->prepare("
            SELECT elem.id AS elem_id, feedback.id AS feedback_id
            FROM `cw_structural_element_feedbacks` AS feedback
            INNER JOIN `cw_structural_elements` AS elem ON (elem.id = feedback.structural_element_id)
            WHERE elem.range_type = 'course'
            AND elem.range_id = :range_id
        ");

        $statement->execute(['range_id' => $cid]);
        $cw_structural_element_feedbacks = $statement->fetchAll();

        foreach ($cw_structural_element_feedbacks as $row) {
            $element = \Courseware\StructuralElement::find($row['elem_id']);
            if (!$element->canEdit($user)) {
                continue;
            }
            if (!$this->arrayHasDataForId($elements, $row['elem_id'])) {
                $elements[] = $element;
                $unit = $element->findUnit();
                $unitElement = $unit->structural_element;
                if (!$this->arrayHasDataForId($elements, $unitElement->id)) {
                    $elements[] = $unitElement;
                }
                if (!$this->arrayHasDataForId($units, $unit->id)) {
                    $units[] = $unit;
                }
            }
            if (!$this->arrayHasDataForId($element_feedbacks, $row['feedback_id'])) {
                $element_feedbacks[] = \Courseware\StructuralElementFeedback::find($row['feedback_id']);
            }
        }

        $encoder = app(\Neomerx\JsonApi\Contracts\Encoder\EncoderInterface::class);

        $data = [
            'units' => $encoder->encodeData($units),
            'elements' => $encoder->encodeData($elements),
            'containers' => $encoder->encodeData($containers),
            'blocks' => $encoder->encodeData($blocks),
            'block_comments' => $encoder->encodeData($block_comments),
            'block_feedbacks' => $encoder->encodeData($block_feedbacks),
            'element_comments' => $encoder->encodeData($element_comments),
            'element_feedbacks' => $encoder->encodeData($element_feedbacks),
        ];
        $this->render_json($data);
    }

    private function arrayHasDataForId(array $array, $id): bool
    {
        $ids = array_column($array, null, 'id');
        return !empty($ids[$id]);
    }

    private function setIndexSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
        SkipLinks::addIndex(_('Aktionen'), 'courseware-action-widget', 21);
        $sidebar->addWidget(new VueWidget('courseware-import-widget'));
    }

    private function setTasksSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-action-widget'));
        SkipLinks::addIndex(_('Aktionen'), 'courseware-action-widget', 21);
    }

    private function setActivitiesSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-activities-widget-filter-type'));
        $sidebar->addWidget(new VueWidget('courseware-activities-widget-filter-unit'));
    }

    private function setCommentsOverviewSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('courseware-comments-overview-widget-filter-type'));
        $sidebar->addWidget(new VueWidget('courseware-comments-overview-widget-filter-created'));
        $sidebar->addWidget(new VueWidget('courseware-comments-overview-widget-filter-unit'));
    }
}
