<?php

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for anonymous_answer_id
 * @property string $anonymous_answer_id database column
 * @property string $questionnaire_id database column
 * @property string $user_id database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property Questionnaire $questionnaire belongs_to Questionnaire
 */
class QuestionnaireAnonymousAnswer extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_anonymous_answers';

        $config['belongs_to']['questionnaire'] = [
            'class_name' => Questionnaire::class,
        ];

        parent::configure($config);
    }

    /**
     * Return a storage object (an instance of the StoredUserData class)
     * enriched with the available data of a given user.
     *
     * @return StoredUserData object
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        $user = User::find($storage->user_id);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(
                    _('Fragebögen anonyme Antworten'),
                    'questionnaire_anonymous_answers',
                    $field_data,
                    $user
                );
            }
        }
        return $storage;
    }
}
