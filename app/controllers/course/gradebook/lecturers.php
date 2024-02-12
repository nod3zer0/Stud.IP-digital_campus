<?php

require_once __DIR__.'/template_helpers.php';

use Grading\Definition;
use Grading\Instance;

/**
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class Course_Gradebook_LecturersController extends AuthenticatedController
{
    use GradebookTemplateHelpers;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$this->viewerIsLecturer()) {
            throw new AccessDeniedException();
        }
        $this->setDefaultPageTitle();
        $this->setupLecturerSidebar();
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function index_action()
    {
        if (Navigation::hasItem('/course/gradebook/index')) {
            Navigation::activateItem('/course/gradebook/index');
        }

        $course = \Context::get();
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->students = $course->getMembersWithStatus('autor', true)->orderBy('nachname, vorname');
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->groupedInstances = $this->groupedInstances($course);
        $this->sumOfWeights = $this->getSumOfWeights($gradingDefinitions);
        $this->totalSums = $this->sumOfWeights ? $this->getTotalSums($gradingDefinitions) : 0;
        $this->totalPassed = $this->getTotalPassed($gradingDefinitions);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function export_action()
    {
        $filename = FileManager::cleanFileName(
            sprintf(
                'gradebook-%s.csv',
                \Context::getHeaderLine()
            )
        );

        $course = \Context::get();
        $this->students = $course->getMembersWithStatus('autor');

        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->groupedInstances = $this->groupedInstances($course);

        $headerLine = ['Nachname', 'Vorname'];
        foreach ($this->categories as $category) {
            $categoryName = Definition::CUSTOM_DEFINITIONS_CATEGORY === $category ? _('Manuell eingetragen') : $category;
            foreach ($this->groupedDefinitions[$category] as $definition) {
                $headerLine[] = $categoryName.': '.$definition->name;
                $headerLine[] = _('bestanden') . '(' . $categoryName.': '.$definition->name . ')';
            }
        }
        $studentLines = [];
        foreach ($this->students as $user) {
            $studentLine = [$user->nachname, $user->vorname];
            foreach ($this->categories as $category) {
                foreach ($this->groupedDefinitions[$category] as $definition) {
                    $studentLine[] = isset($this->groupedInstances[$user->user_id][$definition->id])
                                   ? $this->groupedInstances[$user->user_id][$definition->id]->rawgrade
                                   : 0;
                    $studentLine[] = isset($this->groupedInstances[$user->user_id][$definition->id])
                        ? $this->groupedInstances[$user->user_id][$definition->id]->passed
                        : 0;
                }
            }
            $studentLines[] = $studentLine;
        }

        $data = array_merge([$headerLine], $studentLines);
        $this->render_csv($data, $filename);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function weights_action()
    {
        if (Navigation::hasItem('/course/gradebook/weights')) {
            Navigation::activateItem('/course/gradebook/weights');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->categories = Definition::getCategoriesByCourse($course);
        $this->sumOfWeights = $this->getSumOfWeights($gradingDefinitions);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_weights_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $weights = \Request::intArray('definitions');
        $gradingDefinitions = \SimpleCollection::createFromArray(
            Definition::findByCourse(\Context::get())
        );

        foreach ($gradingDefinitions as $def) {
            if (!isset($weights[$def->id])) {
                continue;
            }
            $newWeight = (int) $weights[$def->id];
            if ($newWeight < 0) {
                continue;
            }
            $def->weight = $newWeight;
        }

        $changedDefinitions = array_filter($gradingDefinitions->store());
        if (count($changedDefinitions)) {
            \PageLayout::postSuccess(_('Gewichtungen erfolgreich verändert.'));
        }
        $this->redirect('course/gradebook/lecturers/weights');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function custom_definitions_action()
    {
        if (Navigation::hasItem('/course/gradebook/custom_definitions')) {
            Navigation::activateItem('/course/gradebook/custom_definitions');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->customDefinitions = $this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY] ?? [];

        $this->students = $course->getMembersWithStatus('autor', true)->orderBy('nachname, vorname');
        $this->groupedInstances = $this->groupedInstances($course);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function store_grades_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        if (Request::submitted('accept')) {
            $course = \Context::get();
            $studentIds = $course->getMembersWithStatus('autor', true)->pluck('user_id');
            $definitionIds = \SimpleCollection::createFromArray(
                Definition::findByCourse($course)
            )->pluck('id');

            $grades = \Request::getArray('grades');
            $passed = \Request::getArray('passed');
            $feedback = \Request::getArray('feedback');
            foreach ($grades as $studentId => $studentGrades) {
                if (!in_array($studentId, $studentIds)) {
                    continue;
                }
                foreach ($studentGrades as $definitionId => $strGrade) {
                    if (!in_array($definitionId, $definitionIds)) {
                        continue;
                    }

                    $instance = new Instance([$definitionId, $studentId]);
                    $instance->rawgrade = ((int)$strGrade) / 100.0;
                    $instance->passed = $passed[$studentId][$definitionId] ?? 0;
                    $instance->feedback = $feedback[$studentId][$definitionId] ?? '';
                    $instance->store();
                }
            }

            \PageLayout::postSuccess(_('Die Noten wurden gespeichert.'));
        } else {
            \PageLayout::postError(_('Beim Übermitteln der Daten trat ein Fehler auf.'));
        }
        $this->redirect('course/gradebook/lecturers/custom_definitions');
    }

    public function edit_custom_definitions_action()
    {
        if (Navigation::hasItem('/course/gradebook/custom_definitions')) {
            Navigation::activateItem('/course/gradebook/edit_custom_definitions');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->customDefinitions = $this->groupedDefinitions[Definition::CUSTOM_DEFINITIONS_CATEGORY] ?? [];
        if (!count($this->customDefinitions )) {
            PageLayout::postInfo(_('Es sind keine manuellen Leistungen definiert.'));
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function new_custom_definition_action()
    {
        // show template
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function create_custom_definition_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $name = trim(\Request::get('name', ''));
        if (!mb_strlen($name)) {
            \PageLayout::postError(_('Der Name einer Leistung darf nicht leer sein.'));
        } else {
            $definition = Definition::create(
                [
                    'course_id' => \Context::getId(),
                    'item' => 'manual',
                    'name' => $name,
                    'tool' => 'manual',
                    'category' => Definition::CUSTOM_DEFINITIONS_CATEGORY,
                    'position' => 0,
                    'weight' => 1.0,
                ]
            );

            if (!$definition) {
                \PageLayout::postError(_('Die Leistung konnte nicht definiert werden.'));
            } else {
                \PageLayout::postSuccess(_('Die Leistung wurde erfolgreich definiert.'));
            }
        }
        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function edit_custom_definition_action($definitionId)
    {
        if (!$this->definition = Definition::findOneBySQL('id = ? AND course_id = ?', [$definitionId, \Context::getId()])) {
            throw new \Trails_Exception(404);
        }

        // show template
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function update_custom_definition_action($definitionId)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$definition = Definition::findOneBySQL('id = ? AND course_id = ?', [$definitionId, \Context::getId()])) {
            throw new \Trails_Exception(404);
        }

        $name = trim(\Request::get('name', ''));
        if (!mb_strlen($name)) {
            \PageLayout::postError(_('Der Name einer Leistung darf nicht leer sein.'));
        } else {
            $definition->name = $name;
            if (!$definition->store()) {
                \PageLayout::postError(_('Die Leistung konnte nicht geändert werden.'));
            }
        }

        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    public function delete_custom_definition_action($definitionId)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$definition = Definition::findOneBySQL(
                'id = ? AND course_id = ?',
                [$definitionId, \Context::getId()]
            )
        ) {
            \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
        } else {
            if (Definition::deleteBySQL('id = ?', [$definition->id])) {
                \PageLayout::postSuccess(_('Die Leistung wurde gelöscht.'));
            } else {
                \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
            }
        }

        $this->redirect('course/gradebook/lecturers/edit_custom_definitions');
    }

    public function edit_ilias_definitions_action()
    {
        if (Navigation::hasItem('/course/gradebook/edit_ilias_definitions')) {
            Navigation::activateItem('/course/gradebook/edit_ilias_definitions');
        }

        $course = \Context::get();
        $gradingDefinitions = Definition::findByCourse($course);
        $this->groupedDefinitions = $this->getGroupedDefinitions($gradingDefinitions);
        $this->customDefinitions = $this->groupedDefinitions['ILIAS'] ?? [];
        $this->setupIliasSidebar(count($this->customDefinitions));
        if (!count($this->customDefinitions )) {
            PageLayout::postInfo(_('Es sind keine ILIAS-Tests als Leistungen definiert.'));
        }
    }

    public function new_ilias_definition_action()
    {
        $this->ilias_modules = [];
        $course = Course::findCurrent();
        $already_defined = new SimpleCollection(Definition::findBySQL("course_id = ? AND category='ILIAS'", [$course->id]));
        foreach (Config::get()->ILIAS_INTERFACE_SETTINGS as $ilias_index => $ilias_config) {
            if ($ilias_config['is_active']) {
                $ilias = new ConnectedIlias($ilias_index);
                $this->ilias_modules[$ilias_index] = array_filter(
                    DBManager::get()->fetchFirst(
                        "SELECT module_id FROM object_contentmodules WHERE object_id=? AND system_type=? AND module_type='tst'", [$course->id, $ilias_index],
                        function ($module_id) use ($ilias, $already_defined) {
                            $item = $ilias->index . '-' . $module_id;
                            if (!$already_defined->findOneBy('item', $item)) {
                                return $ilias->getModule($module_id);
                            }
                            return null;
                        }
                    )
                );
            }
        }
    }

    public function delete_ilias_definition_action($definitionId)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (!$definition = Definition::findOneBySQL(
            'id = ? AND course_id = ?',
            [$definitionId, \Context::getId()]
        )
        ) {
            \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
        } else {
            if (Definition::deleteBySQL('id = ?', [$definition->id])) {
                \PageLayout::postSuccess(_('Die Leistung wurde gelöscht.'));
            } else {
                \PageLayout::postError(_('Die Leistung konnte nicht gelöscht werden.'));
            }
        }

        $this->redirect('course/gradebook/lecturers/edit_ilias_definitions');
    }

    public function create_ilias_definition_action()
    {
        CSRFProtection::verifyUnsafeRequest();
        $ilias_module = Request::get('ilias_module');
        $module_import = Request::int('result') + Request::int('passed');
        if (!$module_import) {
            $module_import = 3;
        }
        if ($ilias_module) {
            [$index, $module_id] = explode('-', $ilias_module);
            $ilias = new ConnectedIlias($index);
            $module = $ilias->getModule($module_id);
            if ($module) {
                $definition = Definition::create(
                    [
                        'course_id' => \Context::getId(),
                        'item'      => $ilias_module . '-' . $module_import,
                        'name'      => $module->getTitle(),
                        'tool'      => 'ILIAS',
                        'category'  => 'ILIAS',
                        'position'  => 0,
                        'weight'    => 0.0,
                    ]
                );

                if (!$definition) {
                    \PageLayout::postError(_('Die Leistung konnte nicht definiert werden.'));
                } else {
                    \PageLayout::postSuccess(_('Die Leistung wurde erfolgreich definiert.'));
                }
            }
        }
        $this->redirect('course/gradebook/lecturers/edit_ilias_definitions');
    }

    public function edit_ilias_definition_action($definition_id)
    {
        $this->definition = Definition::find($definition_id);
        if ($this->definition && Request::submitted('test_name')) {
            CSRFProtection::verifyUnsafeRequest();
            $module_import = Request::int('result') + Request::int('passed');
            [$index, $module_id] = explode('-', $this->definition->item );
            $this->definition->name = Request::get('test_name');
            $this->definition->item = $index . '-' . $module_id . '-' . $module_import;
            if ($this->definition->store()) {
                \PageLayout::postSuccess(_('Die Leistung wurde erfolgreich aktualisiert.'));
            }

            $this->redirect('course/gradebook/lecturers/edit_ilias_definitions');
        }
    }

    public function import_ilias_results_action()
    {
        $num = IliasObjectConnections::importIliasResultsForCourse(Course::findCurrent());
        PageLayout::postInfo(sprintf(
            ngettext(
                '%s Resultat wurde importiert.',
                '%s Resultate wurden importiert.',
                $num),
            $num)
        );
        $this->redirect('course/gradebook/lecturers/edit_ilias_definitions');
    }

    public function getInstanceForUser(Definition $definition, \CourseMember $user)
    {
        if (!isset($this->groupedInstances[$user->user_id])) {
            return null;
        }
        if (!isset($this->groupedInstances[$user->user_id][$definition->id])) {
            return null;
        }

        return $this->groupedInstances[$user->user_id][$definition->id];
    }

    private function groupedInstances($course)
    {
        $gradingInstances = Instance::findByCourse($course);
        $groupedInstances = [];
        foreach ($gradingInstances as $instance) {
            if (!isset($groupedInstances[$instance->user_id])) {
                $groupedInstances[$instance->user_id] = [];
            }
            $groupedInstances[$instance->user_id][$instance->definition_id] = $instance;
        }

        return $groupedInstances;
    }

    private function getTotalSums($gradingDefinitions)
    {
        $gradingDefinitions = \SimpleCollection::createFromArray($gradingDefinitions);
        $totalSums = [];
        foreach ($this->students as $student) {
            if (!isset($totalSums[$student->user_id])) {
                $totalSums[$student->user_id] = 0;
            }

            if (!isset($this->groupedInstances[$student->user_id])) {
                continue;
            }

            foreach ($this->groupedInstances[$student->user_id] as $definitionId => $instance) {
                if ($definition = $gradingDefinitions->findOneBy('id', $definitionId)) {
                    $totalSums[$student->user_id] += $instance->rawgrade * ($definition->weight / $this->sumOfWeights);
                }
            }
        }

        return $totalSums;
    }

    private function getTotalPassed($gradingDefinitions)
    {
        $gradingDefinitions = \SimpleCollection::createFromArray($gradingDefinitions);
        $totalPassed = [];
        foreach ($this->students as $student) {
            if (!isset($totalPassed[$student->user_id])) {
                $totalPassed[$student->user_id] = 0;
            }

            if (!isset($this->groupedInstances[$student->user_id])) {
                continue;
            }

            foreach ($this->groupedInstances[$student->user_id] as $definitionId => $instance) {
                if ($gradingDefinitions->findOneBy('id', $definitionId)) {
                    $totalPassed[$student->user_id] += $instance->passed;
                }
            }
        }
        $count = $gradingDefinitions->count();
        $totalPassed = array_map(
            function($p) use ($count) {
                return $p == $count ? $p : 0;
                }, $totalPassed);
        return $totalPassed;
    }
}
