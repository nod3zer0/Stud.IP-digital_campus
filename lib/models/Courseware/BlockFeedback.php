<?php

namespace Courseware;

use User;

/**
 * Courseware's feedback on blocks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int $id database column
 * @property int $block_id database column
 * @property string $user_id database column
 * @property string $feedback database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \User $user belongs_to \User
 * @property Block $block belongs_to Block
 */
class BlockFeedback extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_block_feedbacks';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['block'] = [
            'class_name' => Block::class,
            'foreign_key' => 'block_id',
        ];

        parent::configure($config);
    }

    public function getStructuralElement(): ?StructuralElement
    {
        $sql = 'SELECT se.*
                FROM cw_block_feedbacks bf
                JOIN cw_blocks b ON b.id = bf.block_id
                JOIN cw_containers c ON c.id = b.container_id
                JOIN cw_structural_elements se ON se.id = c.structural_element_id
                WHERE  bf.id = ?';
        $structuralElement = \DBManager::get()->fetchOne($sql, [$this->getId()]);
        if (!count($structuralElement)) {
            return null;
        }

        return StructuralElement::build($structuralElement, false);
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
            'SELECT * FROM cw_block_feedbacks WHERE user_id = ?',
            [$storage->user_id]
        );
        if ($feedback) {
            $storage->addTabularData(_('Courseware Block Feedback'), 'cw_block_feedback', $feedback);
        }
    }
}
