<?php

namespace Courseware;

use User;

/**
 * Courseware's feedback on structural elements.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.1
 *
 * @property int $id database column
 * @property int $structural_element_id database column
 * @property string $user_id database column
 * @property string $feedback database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \User $user belongs_to \User
 * @property StructuralElement $structural_element belongs_to StructuralElement
 */
class StructuralElementFeedback extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_structural_element_feedbacks';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

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
        $feedback = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_structural_element_feedbacks WHERE user_id = ?',
            [$storage->user_id]
        );
        if ($feedback) {
            $storage->addTabularData(_('Courseware Seiten Feedback'), 'cw_structural_element_feedbacks', $feedback);
        }
    }
}
