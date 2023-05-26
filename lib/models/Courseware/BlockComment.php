<?php

namespace Courseware;

use User;

/**
 * Courseware's comments on blocks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int               $id       database column
 * @property int               $block_id database column
 * @property string            $user_id  database column
 * @property string            $comment  database column
 * @property int               $mkdate   database column
 * @property int               $chdate   database column
 * @property \User             $user     belongs_to User
 * @property \Courseware\Block $block    belongs_to Courseware\Block
 */
class BlockComment extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_block_comments';

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
                FROM cw_block_comments bc
                JOIN cw_blocks b ON b.id = bc.block_id
                JOIN cw_containers c ON c.id = b.container_id
                JOIN cw_structural_elements se ON se.id = c.structural_element_id
                WHERE  bc.id = ?';
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
        $comments = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_block_comments WHERE user_id = ?',
            [$storage->user_id]
        );
        if ($comments) {
            $storage->addTabularData(_('Courseware Block Kommentare'), 'cw_block_comments', $comments);
        }
    }
}
