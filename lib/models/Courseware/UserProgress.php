<?php

namespace Courseware;

/**
 * Courseware's user progresses.
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
 * @property int $block_id database column
 * @property float $grade database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property Block $block belongs_to Block
 * @property \User $user belongs_to \User
 */
class UserProgress extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_user_progresses';

        $config['belongs_to']['block'] = [
            'class_name' => Block::class,
            'foreign_key' => 'block_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'user_id',
        ];

        parent::configure($config);
    }

    /**
     * Returns either an existing UserProgress of this user and block or creates a new one.
     *
     * @param \User $user  the user of the existing or new UserProgress
     * @param Block $block the block of the existing or new UserProgress
     *
     * @return UserProgress the either already existing or new UserProgress
     */
    public static function getUserProgress(\User $user, Block $block): UserProgress
    {
        /** @var ?UserProgress $progress */
        $progress = UserProgress::find([$user->id, $block->id]);
        if (!$progress) {
            /** @var UserProgress $progress */
            $progress = UserProgress::build([
                'user_id' => $user->id,
                'block_id' => $block->id,
                'grade' => 0,
            ]);
        }

        return $progress;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $userProgresses = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_user_progresses WHERE user_id = ?',
            [$storage->user_id]
        );
        if ($userProgresses) {
            $storage->addTabularData(_('Courseware Fortschritt'), 'cw_user_progresses', $userProgresses);
        }
    }
}
