<?php

namespace Courseware;

use User;

/**
* Courseware's task feedback.
*
* @author  Ron Lucke <lucke@elan-ev.de>
* @license GPL2 or any later version
*
* @since   Stud.IP 5.1
*
* @property int                            $id                     database column
* @property int                            $task_id                database column
* @property string                         $lecturer_id            database column
* @property string                         $content                database column
* @property int                            $mkdate                 database column
* @property int                            $chdate                 database column

* @property \User                          $lecturer               belongs_to User
* @property \Courseware\Task               $task                   belongs_to Courseware\Task
*/
class TaskFeedback extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_task_feedbacks';

        $config['belongs_to']['lecturer'] = [
            'class_name' => User::class,
            'foreign_key' => 'lecturer_id',
        ];

        $config['belongs_to']['task'] = [
            'class_name' => Task::class,
            'foreign_key' => 'task_id',
        ];

        parent::configure($config);
    }

    public function getStructuralElement(): ?StructuralElement
    {
        $sql = 'SELECT se.*
                FROM cw_task_feedbacks tf
                JOIN cw_tasks t ON t.id = tf.task_id
                JOIN cw_structural_elements se ON se.id = t.structural_element_id
                WHERE  tf.id = ?';
        $structuralElement = \DBManager::get()->fetchOne($sql, [$this->getId()]);
        if (!count($structuralElement)) {
            return null;
        }

        return StructuralElement::build($structuralElement, false);
    }
}
