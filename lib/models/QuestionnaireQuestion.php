<?php

use eTask\Task;

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for question_id
 * @property string $question_id database column
 * @property string $questionnaire_id database column
 * @property string $questiontype database column
 * @property string|null $internal_name database column
 * @property JSONArrayObject $questiondata database column
 * @property int $position database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property SimpleORMapCollection|QuestionnaireAnswer[] $answers has_many QuestionnaireAnswer
 * @property Questionnaire $questionnaire belongs_to Questionnaire
 */
class QuestionnaireQuestion extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_questions';

        $config['belongs_to']['questionnaire'] = [
            'class_name' => Questionnaire::class,
            'foreign_key' => 'questionnaire_id'
        ];
        $config['has_many']['answers'] = [
            'class_name' => QuestionnaireAnswer::class,
            'on_delete' => 'delete',
            'on_store' => 'store'
        ];
        $config['serialized_fields']['questiondata'] = JSONArrayObject::class;
        parent::configure($config);

    }

    public static function findByQuestionnaire_id($questionnaire_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM questionnaire_questions
            WHERE questionnaire_id = ?
            ORDER BY position ASC
        ");
        $statement->execute([$questionnaire_id]);
        $data = $statement->fetchAll();
        $questions = [];
        foreach ($data as $questionnaire_data) {
            $class = $questionnaire_data['questiontype'];
            if (class_exists(ucfirst($class))) {
                $questions[] = $class::buildExisting($questionnaire_data);
            }
        }
        return $questions;
    }

    public function getMyAnswer($user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        if (!$user_id || $user_id === "nobody") {
            $answer = new QuestionnaireAnswer();
            $answer['user_id'] = $user_id;
            $answer['question_id'] = $this->getId();
            return $answer;
        }
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM questionnaire_answers
            WHERE question_id = :question_id
                AND user_id = :me
        ");
        $statement->execute([
            'question_id' => $this->getId(),
            'me' => $user_id
        ]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return QuestionnaireAnswer::buildExisting($data);
        } else {
            $answer = new QuestionnaireAnswer();
            $answer['user_id'] = $user_id;
            $answer['question_id'] = $this->getId();
            return $answer;
        }
    }

    public function onBeginning()
    {
    }

    public function onEnding()
    {
    }
}
