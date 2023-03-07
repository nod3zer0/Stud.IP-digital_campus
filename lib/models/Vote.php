<?php
require_once 'lib/classes/QuestionType.interface.php';

class Vote extends QuestionnaireQuestion implements QuestionType
{
    public static function getIcon(bool $active = false) : Icon
    {
        return Icon::create(static::getIconShape(), $active ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO);
    }

    /**
     * Returns the shape of the icon of this QuestionType
     * @return string
     */
    public static function getIconShape()
    {
        return 'question-diagram';
    }

    public static function getName()
    {
        return _('Auswahlfrage');
    }

    static public function getEditingComponent()
    {
        return ['vote-edit', ''];
    }

    public function beforeStoringQuestiondata($questiondata)
    {
        $questiondata['description'] = \Studip\Markup::markAsHtml(
            \Studip\Markup::purifyHtml($questiondata['description'])
        );
        $questiondata['options'] = array_filter($questiondata['options'] ?? []);
        return $questiondata;
    }

    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/vote/vote_answer');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createAnswer()
    {
        $answer = $this->getMyAnswer();

        $answers = Request::getArray('answers');
        $userAnswer = 0;
        if (array_key_exists($this->getId(), $answers)) {
            $userAnswer = $answers[$this->getId()]['answerdata']['answers'];
            if (is_array($userAnswer)) {
                $userAnswer = array_map('intval', $userAnswer);
            }
            else {
                $userAnswer = (int) $userAnswer;
            }
        }
        $answer->setData(['answerData' => ['answers' => $userAnswer ] ]);
        return $answer;
    }

    public function getUserIdsOfFilteredAnswer($answer_option)
    {
        $user_ids = [];
        foreach ($this->answers as $answer) {
            $answerData = $answer['answerdata']->getArrayCopy();
            if (in_array($answer_option, (array) $answerData['answers'])) {
                $user_ids[] = $answer['user_id'];
            }
        }
        return $user_ids;
    }

    public function getResultTemplate($only_user_ids = null, $filtered = null)
    {
        $answers = $this->answers;
        if ($only_user_ids !== null) {
            foreach ($answers as $key => $answer) {
                if (!in_array($answer['user_id'], $only_user_ids)) {
                    unset($answers[$key]);
                }
            }
        }
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/vote/vote_evaluation');
        $template->set_attribute('vote', $this);
        $template->set_attribute('answers', $answers);
        $template->set_attribute('filtered', $filtered);
        return $template;
    }

    public function getResultArray()
    {
        $output = [];

        $options = $this['questiondata']['options'] ? $this['questiondata']['options']->getArrayCopy() : [];

        foreach ($options as $key => $option) {
            $answerOption = [];
            $countNobodys = 0;

            foreach ($this->answers as $answer) {
                $answerData = $answer['answerdata']->getArrayCopy();

                if ($answer['user_id'] && $answer['user_id'] != 'nobody') {
                    $userId = $answer['user_id'];
                } else {
                    $countNobodys++;
                    $userId = _('unbekannt').' '.$countNobodys;
                }

                if (in_array($key, (array) $answerData['answers'])) {
                    $answerOption[$userId] = 1;
                } else {
                    $answerOption[$userId] = 0;
                }
            }
            $output[$option] = $answerOption;
        }
        return $output;
    }
}
