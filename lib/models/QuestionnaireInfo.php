<?php
class QuestionnaireInfo extends QuestionnaireQuestion implements QuestionType
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
        return 'question-info';
    }

    public static function getName()
    {
        return _('Information');
    }

    static public function getEditingComponent()
    {
        return ['questionnaire-info-edit', ''];
    }

    public function beforeStoringQuestiondata($questiondata)
    {
        $questiondata['description'] = \Studip\Markup::markAsHtml(
            \Studip\Markup::purifyHtml($questiondata['description'])
        );
        return $questiondata;
    }

    public function getDisplayTemplate()
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/info/info');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function createAnswer()
    {
        return new QuestionnaireAnswer(); // Unused but necessary
    }

    public function getUserIdsOfFilteredAnswer($answer_option)
    {
        return [];
    }

    public function getResultTemplate($only_user_ids = null)
    {
        $factory = new Flexi_TemplateFactory(realpath(__DIR__.'/../../app/views'));
        $template = $factory->open('questionnaire/question_types/info/info');
        $template->set_attribute('vote', $this);
        return $template;
    }

    public function getResultArray()
    {
        return [];
    }

    /**
     * Return whether a given url is valid.
     * @return bool
     */
    public function hasValidURL(): bool
    {
        return !empty($this->questiondata['url'])
            && trim($this->questiondata['url'])
            && filter_var($this->questiondata['url'], FILTER_VALIDATE_URL);
    }
}
