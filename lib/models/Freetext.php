<?php

require_once 'lib/classes/QuestionType.interface.php';

class Freetext extends QuestionnaireQuestion implements QuestionType
{
    /**
     * Returns the Icon-object to this QuestionType.
     * @param bool $active: true if Icon should be clickable, false for black info-icon.
     * @return Icon : guestbook-icon.
     */
    public static function getIcon(bool $active = false) : Icon
    {
        return Icon::create(
            static::getIconShape(),
            $active ? Icon::ROLE_CLICKABLE : Icon::ROLE_INFO
        );
    }

    /**
     * Returns the shape of the icon of this QuestionType
     * @return string
     */
    public static function getIconShape()
    {
        return 'question-text';
    }

    /**
     * Returns the name of this QuestionType "Freitextfrage".
     * @return string
     */
    public static function getName()
    {
        return _('Freitextfrage');
    }

    static public function getEditingComponent()
    {
        return ['freetext-edit', ''];
    }

    public function beforeStoringQuestiondata($questiondata)
    {
        $questiondata['description'] = \Studip\Markup::markAsHtml(
            \Studip\Markup::purifyHtml($questiondata['description'])
        );
        return $questiondata;
    }

    /**
     * Returns the template of this question to answer the question.
     * @return Flexi_Template
     * @throws Flexi_TemplateNotFoundException if there is no template.
     */
    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__ . '/../../app/views'));
        $template = $factory->open('questionnaire/question_types/freetext/freetext_answer.php');
        $template->vote = $this;
        return $template;
    }

    /**
     * Creates an answer by the parameters of the request. Called when a user clicked to answer
     * the questionnaire.
     * @return QuestionnaireAnswer
     */
    public function createAnswer()
    {
        $answer = $this->getMyAnswer();
        $answers = Request::getArray('answers');
        $userAnswerText = $answers[$this->getId()]['answerdata']['text'];
        $answer->setData(['answerData' => ['text' => $userAnswerText]]);
        return $answer;
    }

    public function getUserIdsOfFilteredAnswer($answer_option)
    {
        return [];
    }

    /**
     * Returns the template with the answers of the question so far.
     * @param null $only_user_ids : array of user_ids
     * @return Flexi_Template
     * @throws Flexi_TemplateNotFoundException if there is no template.
     */
    public function getResultTemplate($only_user_ids = null)
    {
        $answers = $this->answers;
        if ($only_user_ids !== null) {
            foreach ($answers as $key => $answer) {
                if (!in_array($answer['user_id'], $only_user_ids)) {
                    unset($answers[$key]);
                }
            }
        }
        $factory = new Flexi_TemplateFactory(realpath(__DIR__ . '/../../app/views'));
        $template = $factory->open('questionnaire/question_types/freetext/freetext_evaluation.php');
        $template->vote = $this;
        $template->set_attribute('answers', $answers);
        return $template;
    }

    /**
     * Returns an array of the answers to be put into a CSV-file.
     * @return array
     */
    public function getResultArray()
    {
        $output = [];
        $countNobodys = 0;

        $question = trim(strip_tags($this->questiondata['description']));
        foreach ($this->answers as $answer) {
            if ($answer['user_id'] && $answer['user_id'] != 'nobody') {
                $userId = $answer['user_id'];
            } else {
                $countNobodys++;
                $userId = _('unbekannt').' '.$countNobodys;
            }

            $output[$question][$userId] = $answer['answerdata']['text'];
        }

        return $output;
    }

    /**
     * Called after the questionnaire gets closed. Does nothing for this QuestionType Freetext.
     */
    public function onEnding()
    {
        //Nothing to do here.
    }
}
