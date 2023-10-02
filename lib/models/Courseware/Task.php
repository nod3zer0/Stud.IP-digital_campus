<?php

namespace Courseware;

use User;

/**
 * Courseware's tasks.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.1
 *
 * @property int $id database column
 * @property int $task_group_id database column
 * @property int $structural_element_id database column
 * @property string $solver_id database column
 * @property string|null $solver_type database column
 * @property int $submission_date database column
 * @property int $submitted database column
 * @property string|null $renewal database column
 * @property int $renewal_date database column
 * @property int|null $feedback_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property TaskGroup $task_group belongs_to TaskGroup
 * @property StructuralElement $structural_element belongs_to StructuralElement
 * @property \User $lecturer belongs_to \User
 * @property \User $user belongs_to \User
 * @property \Statusgruppen $group belongs_to \Statusgruppen
 * @property \Course $course belongs_to \Course
 * @property TaskFeedback|null $task_feedback belongs_to TaskFeedback
 * @property mixed $solver additional field
 */
class Task extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_tasks';

        $config['belongs_to']['task_group'] = [
            'class_name' => TaskGroup::class,
            'foreign_key' => 'task_group_id',
        ];

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        $config['belongs_to']['lecturer'] = [
            'class_name' => User::class,
            'foreign_key' => 'lecturer_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'solver_id',
            'assoc_foreign_key' => 'user_id',
        ];

        $config['belongs_to']['group'] = [
            'class_name' => \Statusgruppen::class,
            'foreign_key' => 'solver_id',
            'assoc_foreign_key' => 'statusgruppe_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name' => \Course::class,
            'foreign_key' => 'seminar_id',
        ];

        $config['belongs_to']['task_feedback'] = [
            'class_name' => TaskFeedback::class,
            'foreign_key' => 'feedback_id',
        ];

        $config['additional_fields']['solver'] = [
            'get' => 'getSolver',
            'set' => false,
        ];

        parent::configure($config);
    }

    /**
     * Returns the structural element of this task.
     * This structural element and all its children are part of the task.
     *
     * @return StructuralElement the structural element
     */
    public function getStructuralElement(): StructuralElement
    {
        return $this->structural_element;
    }

    /**
     * Returns the feedback for this task.
     *
     * @return TaskFeedback the task feedback
     */
    public function getFeedback(): ?TaskFeedback
    {
        return $this->task_feedback;
    }

    /**
     * @return bool true if task is submitted
     */
    public function isSubmitted(): bool
    {
        return 1 === (int) $this->submitted;
    }

    /**
     * @param \User|\Seminar_User $user
     */
    public function canUpdate($user): bool
    {
        $perm = false;
        switch ($this->solver_type) {
            case 'autor':
                if ($this->solver_id === $user->id) {
                    return true;
                }
                break;

            case 'group':
                $group = \Statusgruppen::find($this->solver_id);
                if (isset($group) && $group->isMember($user->id)) {
                    return true;
                }
                break;
        }

        return $this->getStructuralElement()->hasEditingPermission($user);
    }

    /**
     * @param \User|\Seminar_User $user
     */
    public function userIsASolver($user): bool
    {
        switch ($this->solver_type) {
            case 'autor':
                return $this->solver_id === $user->id;

            case 'group':
                $group = $this->getSolver();

                return $group->isMember($user->id);
        }

        return false;
    }

    /**
     * @return \User|\Statusgruppen|null the solver
     */
    public function getSolver()
    {
        switch ($this->solver_type) {
            case 'autor':
                return \User::find($this->solver_id);

            case 'group':
                return \Statusgruppen::find($this->solver_id);
        }

        return null;
    }

    public function getTaskProgress(): float
    {
        $children = $this->structural_element->findDescendants();

        $element_counter = 1;
        $progress = $this->getStructuralElementProgress($this->structural_element);
        foreach ($children as $child) {
            ++$element_counter;
            $progress = ($this->getStructuralElementProgress($child) + $progress) / $element_counter;
        }

        return $progress * 100;
    }

    private function getStructuralElementProgress(StructuralElement $structural_element): float
    {
        $containers = Container::findBySQL('structural_element_id = ?', [intval($structural_element->id)]);
        $counter = 0;
        $progress = 0;
        $b = [];
        foreach ($containers as $container) {
            $blockCount = $container->countBlocks();

            $counter += $blockCount;
            if ($blockCount > 0) {
                $blocks = Block::findBySQL('container_id = ?', [$container->id]);
                foreach ($blocks as $block) {
                    $b[] = $block->id;
                    if ($this->task_group->lecturer_id === $block->owner_id && $block->owner_id !== $block->editor_id) {
                        ++$progress;
                    }
                }
            }
        }
        if ($counter > 0) {
            return $progress / $counter;
        }

        return 0;
    }
}
