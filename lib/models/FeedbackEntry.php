<?php

/**
 *
 * @author Nils Gehrke <nils.gehrke@uni-goettingen.de>
 * @author Ron Lucke <lucke@elan-ev.de>
 *
 * @property int $id database column
 * @property int $feedback_id database column
 * @property string $user_id database column
 * @property string $comment database column
 * @property int $rating database column
 * @property int $anonymous database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property FeedbackElement $feedback belongs_to FeedbackElement
 * @property User $user belongs_to User
 */

class FeedbackEntry extends SimpleORMap
{
    public static function configure($config = [])
    {
        $config['db_table'] = 'feedback_entries';

        $config['belongs_to']['feedback'] = [
            'class_name'    => FeedbackElement::class,
            'foreign_key'   => 'feedback_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];

        parent::configure($config);
    }

    public function isEditable()
    {
        return $this->user_id === $GLOBALS['user']->id;
    }

    public function isDeletable()
    {
        $deletable = false;

        $user_id = $GLOBALS['user']->id;

        if ($this->user_id == $user_id) {
            $deletable = true;
        } else {
            $course_id = $this->feedback->course_id;
            $perm_level = \CourseConfig::get($course_id)->FEEDBACK_ADMIN_PERM;
            if ($GLOBALS['perm']->have_studip_perm($perm_level, $course_id)) {
                $deletable = true;
            }
        }
        return $deletable;
    }

    public function delete()
    {
        if ($this->isDeletable()) {
            parent::delete();
        }
        return $this->is_deleted;
    }
}
