<?php

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for answer_id
 * @property string $answer_id database column
 * @property string $question_id database column
 * @property string|null $user_id database column
 * @property JSONArrayObject $answerdata database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property QuestionnaireQuestion $question belongs_to QuestionnaireQuestion
 * @property User|null $user belongs_to User
 */
class QuestionnaireAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_answers';

        $config['belongs_to']['question'] = [
            'class_name' => QuestionnaireQuestion::class,
        ];
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id'
        ];
        $config['serialized_fields']['answerdata'] = JSONArrayObject::class;

        parent::configure($config);
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
                $storage->addTabularData(_('Fragebögen Antworten'), 'questionnaire_answers', $field_data);
            }
        }
    }
}
