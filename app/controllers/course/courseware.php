<?php

use Courseware\StructuralElement;
use Courseware\Instance;
use Courseware\UserProgress;

/**
 * @property ?string $entry_element_id
 * @property int $last_visitdate
 * @property mixed $courseware_progress_data
 * @property mixed $courseware_chapter_counter
 */
class Course_CoursewareController extends AuthenticatedController
{
    protected $_autobind = true;

    public function before_filter(&$action, &$args)
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

    public function index_action()
    {
        /** @var array<mixed> $last */
        $last = UserConfig::get($GLOBALS['user']->id)->getValue('COURSEWARE_LAST_ELEMENT');
        if (isset($last[Context::getId()])) {
            $this->entry_element_id = $last[Context::getId()];
            /** @var ?StructuralElement $struct */
            $struct = StructuralElement::findOneBySQL("id = ? AND range_id = ? AND range_type = 'course'", [
                $this->entry_element_id,
                Context::getId(),
            ]);
        }

        // load courseware for course
        if (!$this->entry_element_id || !$struct || !$struct->canRead($GLOBALS['user'])) {
            $course = Course::find(Context::getId());

            if (!$course->courseware) {
                // create initial courseware dataset
                StructuralElement::createEmptyCourseware(Context::getId(), 'course');
            }

            $this->entry_element_id = $course->courseware->id;
        }

        $last[Context::getId()] = $this->entry_element_id;
        UserConfig::get($GLOBALS['user']->id)->store('COURSEWARE_LAST_ELEMENT', $last);

        Navigation::activateItem('course/courseware/content');
        $this->setIndexSidebar();
        $this->licenses = [];
        $sorm_licenses = License::findBySQL('1 ORDER BY name ASC');
        foreach ($sorm_licenses as $license) {
            array_push($this->licenses, $license->toArray());
        }
        $this->licenses = json_encode($this->licenses);
    }

    public function dashboard_action(): void
    {
        global $perm, $user;
        $course_progress = $perm->have_studip_perm('dozent', Context::getId(), $user->id);
        $this->courseware_progress_data = $this->getProgressData($course_progress);
        $this->courseware_chapter_counter = $this->getChapterCounter($this->courseware_progress_data);
        Navigation::activateItem('course/courseware/dashboard');
    }

    public function manager_action(): void
    {
        $courseId = Context::getId();
        $element = StructuralElement::getCoursewareCourse($courseId);
        $instance = new Instance($element);
        if (!$GLOBALS['perm']->have_studip_perm($instance->getEditingPermissionLevel(), $courseId)) {
            $this->redirect('course/courseware/index');
        } else {
            Navigation::activateItem('course/courseware/manager');
        }
    }

    private function setIndexSidebar(): void
    {
        $sidebar = Sidebar::Get();
        $actions = new TemplateWidget(
            _('Aktionen'),
            $this->get_template_factory()->open('course/courseware/action_widget')
        );
        $sidebar->addWidget($actions)->addLayoutCSSClass('courseware-action-widget');

        $views = new TemplateWidget(
            _('Ansichten'),
            $this->get_template_factory()->open('course/courseware/view_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-view-widget');
    }

    private function getProgressData(bool $showProgressForAllParticipants = false): iterable
    {
        /** @var ?\Course $course */
        $course = Context::get();
        if (!$course || !$course->courseware) {
            return [];
        }

        $instance = new Instance($course->courseware);
        $user = \User::findCurrent();

        $elements = $this->findElements($instance, $user);
        $progress = $this->computeSelfProgresses($instance, $user, $elements, $showProgressForAllParticipants);
        $progress = $this->computeCumulativeProgresses($instance, $elements, $progress);

        return $this->prepareProgressData($elements, $progress);
    }

    private function findElements(Instance $instance, User $user): iterable
    {
        $elements = $instance->getRoot()->findDescendants($user);
        $elements[] = $instance->getRoot();

        return array_combine(array_column($elements, 'id'), $elements);
    }

    private function computeChildrenOf(iterable $elements): iterable
    {
        $childrenOf = [];
        foreach ($elements as $elementId => $element) {
            if ($element['parent_id']) {
                if (!isset($childrenOf[$element['parent_id']])) {
                    $childrenOf[$element['parent_id']] = [];
                }
                $childrenOf[$element['parent_id']][] = $elementId;
            }
        }

        return $childrenOf;
    }

    private function computeSelfProgresses(
        Instance $instance,
        User $user,
        iterable $elements,
        bool $showProgressForAllParticipants
    ): iterable {
        $progress = [];
        /** @var \Course $course */
        $course = $instance->getRange();
        $allBlocks = $instance->findAllBlocksGroupedByStructuralElementId();
        $courseMemberIds = $showProgressForAllParticipants
            ? array_column($course->getMembersWithStatus('autor'), 'user_id')
            : [$user->getId()];
        $userProgresses = UserProgress::findBySQL('user_id IN (?)', [$courseMemberIds]);
        foreach ($elements as $elementId => $element) {
            $selfProgress = $this->getSelfProgresses($allBlocks, $elementId, $userProgresses, $courseMemberIds);
            $progress[$elementId] = [
                'self' => $selfProgress['counter'] ? $selfProgress['progress'] / $selfProgress['counter'] : 1,
            ];
        }

        return $progress;
    }

    private function getSelfProgresses(
        array $allBlocks,
        string $elementId,
        array $userProgresses,
        array $courseMemberIds
    ): array {
        $blks = $allBlocks[$elementId] ?: [];

        $data = [
            'counter' => count($blks),
            'progress' => 0,
        ];
        $usersCounter = count($courseMemberIds);
        foreach ($blks as $blk) {
            $progresses = array_filter($userProgresses, function ($progress) use ($blk, $courseMemberIds) {
                return $progress->block_id === $blk->getId() && in_array($progress->user_id, $courseMemberIds);
            });
            $usersProgress = count($progresses) ? array_sum(array_column($progresses, 'grade')) : 0;

            $data['progress'] += $usersProgress / $usersCounter;
        }

        return $data;
    }

    private function computeCumulativeProgresses(Instance $instance, iterable $elements, iterable $progress): iterable
    {
        $childrenOf = $this->computeChildrenOf($elements);

        // compute `cumulative` of each element
        $visitor = function (&$progress, $element) use (&$childrenOf, &$elements, &$visitor) {
            $elementId = $element->getId();
            $numberOfNodes = 0;
            $cumulative = 0;

            // visit children first
            if (isset($childrenOf[$elementId])) {
                foreach ($childrenOf[$elementId] as $childId) {
                    $visitor($progress, $elements[$childId]);
                    $numberOfNodes += $progress[$childId]['numberOfNodes'];
                    $cumulative += $progress[$childId]['cumulative'];
                }
            }

            $progress[$elementId]['cumulative'] = $cumulative + $progress[$elementId]['self'];
            $progress[$elementId]['numberOfNodes'] = $numberOfNodes + 1;

            return $progress;
        };

        $visitor($progress, $instance->getRoot());

        return $progress;
    }

    private function prepareProgressData(iterable $elements, iterable $progress): iterable
    {
        $data = [];
        foreach ($elements as $elementId => $element) {
            $elementProgress = $progress[$elementId];
            $cumulative = $elementProgress['cumulative'] / $elementProgress['numberOfNodes'];

            $data[$elementId] = [
                'id' => (int) $elementId,
                'parent_id' => (int) $element['parent_id'],
                'name' => $element['title'],
                'progress' => [
                    'cumulative' => round($cumulative, 2) * 100,
                    'self' => round($elementProgress['self'], 2) * 100,
                ],
            ];
        }

        return $data;
    }

    private function getChapterCounter(array $chapters): array
    {
        $finished = 0;
        $started = 0;
        $ahead = 0;

        foreach ($chapters as $chapter) {
            if ($chapter['parent_id'] != null) {
                if ($chapter['progress']['self'] == 0) {
                    $ahead += 1;
                }
                if ($chapter['progress']['self'] > 0 && $chapter['progress']['self'] < 100) {
                    $started += 1;
                }
                if ($chapter['progress']['self'] == 100) {
                    $finished += 1;
                }
            }
        }

        return [
            'started' => $started,
            'finished' => $finished,
            'ahead' => $ahead,
        ];
    }
}
