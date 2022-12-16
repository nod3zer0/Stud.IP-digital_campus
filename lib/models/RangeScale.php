<?php
require_once 'lib/classes/QuestionType.interface.php';

class RangeScale extends QuestionnaireQuestion implements QuestionType
{
    public static function getIcon(bool $active = false) : Icon
    {
        return Icon::create(static::getIconShape(), $active ? 'clickable' : 'info');
    }

    /**
     * Returns the shape of the icon of this QuestionType
     * @return string
     */
    public static function getIconShape()
    {
        return 'rangescale';
    }

    public static function getName()
    {
        return _('Pol-Skala');
    }

    public function beforeStoringQuestiondata($questiondata)
    {
        $questiondata['description'] = \Studip\Markup::markAsHtml(
            \Studip\Markup::purifyHtml($questiondata['description'])
        );
        $questiondata['statements'] = array_filter($questiondata['statements']);
        return $questiondata;
    }

    static public function getEditingComponent()
    {
        return ['rangescale-edit', ''];
    }

    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/rangescale/rangescale_answer');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createAnswer()
    {
        $answer = $this->getMyAnswer();

        $answers = Request::getArray('answers');
        $userAnswer = (array) $answers[$this->getId()]['answerdata']['answers'];
        $answer->setData(['answerdata' => ['answers' => $userAnswer ] ]);
        return $answer;
    }

    public function getUserIdsOfFilteredAnswer($answer_option)
    {
        $user_ids = [];
        list($statement_key, $options_key) = explode('_', $answer_option);
        foreach ($this->answers as $answer) {
            $answerData = $answer['answerdata']->getArrayCopy();
            if ($answerData['answers'][$statement_key] == $options_key) {
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
        $template = $factory->open('questionnaire/question_types/rangescale/rangescale_evaluation');
        $template->set_attribute('vote', $this);
        $template->set_attribute('answers', $answers);
        $template->set_attribute('filtered', $filtered);
        return $template;
    }

    public function getResultArray()
    {
        $output = [];

        $statements = $this['questiondata']['statements']->getArrayCopy();

        foreach ($statements as $statement_key => $statement) {
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

                $answerOption[$userId] = $answerData['answers'][$statement_key];
            }
            $output[$statement] = $answerOption;
        }
        return $output;
    }
}
