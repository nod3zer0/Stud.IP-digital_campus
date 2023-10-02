<?php

namespace Courseware;

/**
 * Courseware's bookmarks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property array $id alias for pk
 * @property string $user_id database column
 * @property int $element_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \User $user belongs_to \User
 * @property StructuralElement $element belongs_to StructuralElement
 */
class Bookmark extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_bookmarks';

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'element_id',
        ];

        parent::configure($config);
    }

    /**
     * Returns the range object this bookmark belongs to.
     *
     * @return \Range the range object of this object
     */
    public function getRange(): \Range
    {
        $rangeType = $this->element['range_type'];

        return $this->element->$rangeType;
    }

    /**
     * Returns all bookmarks of a user.
     *
     * @param \User $user the user for whom to search for bookmarks
     *
     * @return Bookmark[] the list of bookmarks
     */
    public static function findUsersBookmarks($user): array
    {
        return self::findBySQL('user_id = ? ORDER BY chdate', [$user->id]);
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $bookmarks = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_bookmarks WHERE user_id = ?',
            [$storage->user_id]
        );
        if ($bookmarks) {
            $storage->addTabularData(_('Courseware Lesezeichen'), 'cw_bookmarks', $bookmarks);
        }
    }
}
