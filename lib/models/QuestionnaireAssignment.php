<?php

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for assignment_id
 * @property string $assignment_id database column
 * @property string $questionnaire_id database column
 * @property string $range_id database column
 * @property string $range_type database column
 * @property string $user_id database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property Questionnaire $questionnaire belongs_to Questionnaire
 */
class QuestionnaireAssignment extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'questionnaire_assignments';

        $config['belongs_to']['questionnaire'] = [
            'class_name'        => Questionnaire::class,
            'foreign_key'       => 'questionnaire_id',
            'assoc_foreign_key' => 'questionnaire_id',
        ];

        parent::configure($config);
    }

    public static function findBySeminarAndQuestionnaire($seminar_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'course'", [$questionnaire_id, $seminar_id]);
    }

    public static function findByInstituteAndQuestionnaire($institute_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'institute'", [$questionnaire_id, $institute_id]);
    }

    public static function findByStatusgruppeAndQuestionnaire($statusgruppe_id, $questionnaire_id)
    {
        return self::findOneBySQL("questionnaire_id = ? AND range_id = ? AND range_type = 'statusgruppe'", [$questionnaire_id, $statusgruppe_id]);
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
                $storage->addTabularData(_('Fragebögen Zuweisungen'), 'questionnaire_assignments', $field_data);
            }
        }
    }
}
