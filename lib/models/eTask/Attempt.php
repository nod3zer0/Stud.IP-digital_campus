<?php
namespace eTask;

use JSONArrayObject;

/**
 * eTask conforming assignment attempt definition.
 *
 * @property int $id database column
 * @property int $assignment_id database column
 * @property string $user_id database column
 * @property int|null $start database column
 * @property int|null $end database column
 * @property \JSONArrayObject $options database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property Assignment $assignment belongs_to Assignment
 */
class Attempt extends \SimpleORMap implements \PrivacyObject
{
    use ConfigureTrait;

    /**
     * @see SimpleORMap::configure
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'etask_assignment_attempts';

        $config['relationTypes'] = self::configureClassNames($config);

        $config['belongs_to']['assignment'] = [
            'class_name' => $config['relationTypes']['Assignment'],
            'foreign_key' => 'assignment_id'
        ];

        $config['serialized_fields']['options'] = JSONArrayObject::class;

        parent::configure($config);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('eTask Zuweisungen'), 'etask_assignment_attempts', $field_data);
            }
        }
    }
}
