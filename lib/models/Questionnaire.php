<?php

class Questionnaire extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaires';

        $config['has_many']['questions'] = [
            'class_name' => QuestionnaireQuestion::class,
            'order_by' => 'ORDER BY position ASC',
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['assignments'] = [
            'class_name' => QuestionnaireAssignment::class,
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['has_many']['anonymousanswers'] = [
            'class_name' => QuestionnaireAnonymousAnswer::class,
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];

        parent::configure($config);
    }

    public $answerable;

    public function countAnswers()
    {
        $statement = DBManager::get()->prepare("
            SELECT COUNT(*) as `count_answers`
            FROM questionnaire_answers
                INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
            WHERE questionnaire_id = :questionnaire_id
            GROUP BY questionnaire_answers.question_id
            ORDER BY `count_answers` DESC
            LIMIT 1
        ");
        $statement->execute([
            'questionnaire_id' => $this->getId()
        ]);
        $answers_total = $statement->fetch(PDO::FETCH_COLUMN, 0);

        return $answers_total;
    }

    public function isAnswered($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if (!$user_id || ($user_id === "nobody")) {
            return false;
        }
        $statement = DBManager::get()->prepare("
            SELECT 1
            FROM questionnaire_answers
                INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
            WHERE user_id = :user_id
                AND questionnaire_id = :questionnaire_id
            UNION SELECT 1
            FROM questionnaire_anonymous_answers
            WHERE user_id = :user_id
                AND questionnaire_id = :questionnaire_id
        ");
        $statement->execute([
            'user_id' => $user_id,
            'questionnaire_id' => $this->getId()
        ]);
        return (bool) $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function latestAnswerTimestamp()
    {
        $statement = DBManager::get()->prepare("
            SELECT questionnaire_answers.chdate
            FROM questionnaire_answers
                INNER JOIN questionnaire_questions ON (questionnaire_answers.question_id = questionnaire_questions.question_id)
            WHERE questionnaire_questions.questionnaire_id = ?
            ORDER BY questionnaire_answers.chdate DESC
            LIMIT 1
        ");
        $statement->execute([$this->getId()]);
        return $statement->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function isViewable()
    {
        if ($this->isEditable()) {
            return true;
        }
        if (!$this->isStarted()) {
            return false;
        }
        foreach ($this->assignments as $assignment) {
            if ($assignment['range_id'] === "public") {
                return true;
            } elseif (in_array($assignment['range_type'], ["static", "user", "institute"]) && $GLOBALS['perm']->have_perm("user")) {
                return true;
            } elseif ($assignment['range_type'] === "statusgruppe") {

                $statusgruppe_user = StatusgruppeUser::findOneBySQL(
                    "statusgruppe_id = ? AND user_id = ?",
                    [$assignment['range_id'], $GLOBALS['user']->id]);
                if ($statusgruppe_user) {
                    return true;
                }
            } elseif($GLOBALS['perm']->have_studip_perm("user", $assignment['range_id'])) {
                return true;
            } else {
                //now look through all plugin if this assignment is related to plugin contents:
                foreach (PluginManager::getInstance()->getPlugins("QuestionnaireAssignmentPlugin") as $plugin) {
                    if ($plugin->isQuestionnaireViewable($assignment)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function isAnswerable()
    {
        if (!$this->isViewable() || !$this->isRunning()) {
            return false;
        }
        if ($this['anonymous'] && $this->isAnswered()) {
            return false;
        }
        if ($this->isEditable()) {
            return true;
        }
        $this->answerable = true;
        NotificationCenter::postNotification("QuestionnaireWillAllowToAnswer", $this);
        return $this->answerable;
    }

    public function isEditable()
    {
        if ($this->isNew() || ($this['user_id'] === $GLOBALS['user']->id) || $GLOBALS['perm']->have_perm("root")) {
            return true;
        } else {
            foreach ($this->assignments as $assignment) {
                if ($assignment['range_type'] === "institute" && $GLOBALS['perm']->have_studip_perm("tutor", $assignment['range_id'])) {
                    return true;
                } elseif ($assignment['range_type'] === "statusgruppe") {
                    $statusgruppe = Statusgruppen::find($assignment['range_id']);
                    if ($statusgruppe && $GLOBALS['perm']->have_studip_perm("tutor", $statusgruppe['range_id'])) {
                        return true;
                    }
                } elseif($assignment['range_type'] === "course" && $GLOBALS['perm']->have_studip_perm("tutor", $assignment['range_id'])) {
                    return true;
                } else {
                    //now look through all plugin if this assignment is related to plugin contents:
                    foreach (PluginManager::getInstance()->getPlugins("QuestionnaireAssignmentPlugin") as $plugin) {
                        if ($plugin->isQuestionnaireEditable($assignment)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    public function isCopyable()
    {
        return ($this->copyable && $GLOBALS['perm']->have_perm('autor') && $this->isViewable()) || $this->isEditable();
    }

    public function start()
    {
        if (!$this->isRunning()) {
            $this['startdate'] = time();
            $this['visible'] = 1;
            if ($this->isStopped()) {
                $this['stopdate'] = null;
            }
            $this->store();
            foreach ($this->questions as $question) {
                $question->onBeginning();
            }
        }
    }

    public function stop()
    {
        if (!$this->isStopped()) {
            $this['visible'] = $this['resultvisibility'] === 'never' ? 0 : 1;
            $this['stopdate'] = time();
            $this->store();
            foreach ($this->questions as $question) {
                $question->onEnding();
            }
        }
    }

    public function isStarted()
    {
        return $this['startdate'] && ($this['startdate'] <= time());
    }

    public function isStopped()
    {
        return $this['stopdate'] && ($this['stopdate'] <= time());
    }

    public function isRunning()
    {
        return $this->isStarted() && !$this->isStopped();
    }

    public function resultsVisible()
    {
        if (!$this->isViewable()) {
            return false;
        }
        return $this['resultvisibility'] === "always"
            || $this->isEditable()
            || ($this['resultvisibility'] === "afterending" && $this->isStopped())
            || ($this['resultvisibility'] === 'afterparticipation' && $this->isAnswered());
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Fragebögen'), 'questionnaires', $field_data);
            }
        }
    }

    /**
     * Returns all data as an array that could be stored as JSON.
     * @return array
     */
    public function exportAsFile()
    {
        $data = [
            'questionnaire' => [
                'title' => $this['title'],
                'anonymous' => $this['anonymous'],
                'resultvisibility' => $this['resultvisibility'],
                'editanswers' => $this['editanswers']
            ],
            'questions_data' => []
        ];
        foreach ($this->questions as $question) {
            $data['questions_data'][] = [
                'questiontype' => $question['questiontype'],
                'internal_name' => $question['internal_name'],
                'questiondata' => $question['questiondata']->getArrayCopy()
            ];
        }
        return $data;
    }
}
