<?php

namespace Courseware;

use User;

/**
 * Courseware's blocks.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int $id database column
 * @property int $container_id database column
 * @property string $owner_id database column
 * @property string $editor_id database column
 * @property string|null $edit_blocker_id database column
 * @property int $position database column
 * @property string|null $block_type database column
 * @property int $visible database column
 * @property int $commentable database column
 * @property string $payload database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property \SimpleORMapCollection|UserDataField[] $data_fields has_many UserDataField
 * @property \SimpleORMapCollection|BlockComment[] $comments has_many BlockComment
 * @property \SimpleORMapCollection|BlockFeedback[] $block_feedback has_many BlockFeedback
 * @property \SimpleORMapCollection|UserProgress[] $progresses has_many UserProgress
 * @property \User $owner belongs_to \User
 * @property \User $editor belongs_to \User
 * @property \User|null $edit_blocker belongs_to \User
 * @property Container $container belongs_to Container
 * @property mixed $type additional field
 * @property-read mixed $files additional field
 */
class Block extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_blocks';

        $config['belongs_to']['owner'] = [
            'class_name' => User::class,
            'foreign_key' => 'owner_id',
        ];

        $config['belongs_to']['editor'] = [
            'class_name' => User::class,
            'foreign_key' => 'editor_id',
        ];

        $config['belongs_to']['edit_blocker'] = [
            'class_name' => User::class,
            'foreign_key' => 'edit_blocker_id',
        ];

        $config['belongs_to']['container'] = [
            'class_name' => Container::class,
            'foreign_key' => 'container_id',
        ];

        $config['has_many']['data_fields'] = [
            'class_name' => UserDataField::class,
            'assoc_foreign_key' => 'block_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        $config['has_many']['comments'] = [
            'class_name' => BlockComment::class,
            'assoc_foreign_key' => 'block_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        $config['has_many']['block_feedback'] = [
            'class_name' => BlockFeedback::class,
            'assoc_foreign_key' => 'block_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        $config['has_many']['progresses'] = [
            'class_name' => UserProgress::class,
            'assoc_foreign_key' => 'block_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        $config['additional_fields']['type'] = [
            'get' => function ($block) {
                return BlockTypes\BlockType::factory($block);
            },
            'set' => false,
        ];

        $config['additional_fields']['files'] = [
            'get' => function ($block) {
                return array_filter($block->type->getFiles(), function ($file_ref) {
                    if ($file_ref) {
                        $file = $file_ref->getFileType();
                        return $file->isDownloadable();
                    } else {
                        return false;
                    }
                });
            },
        ];

        parent::configure($config);
    }

    /**
     * Returns whether this block is blocked from editing.
     */
    public function isBlocked(): bool
    {
        return null != $this->edit_blocker;
    }

    /**
     * Returns who is blocking this block from editing if at all.
     *
     * @return ?string either the blocker's ID or null if this block is not blocked
     */
    public function getBlockerUserId(): ?string
    {
        if ($this->isBlocked()) {
            return $this->edit_blocker->id;
        } else {
            return null;
        }
    }

    /**
     * Activates the edit block for a user.
     *
     * @param string $userId the ID of the user blocking this block
     */
    public function setBlockerId(string $userId): void
    {
        $this->edit_blocker_id = $userId;
    }

    /**
     * Returns the full name of the user currently blocking this block.
     *
     * @return string the full name of the user currently blocking this block
     */
    public function getBlockerName(): string
    {
        /** @var \User $user */
        $user = User::find($this->edit_blocker_id);

        return $user->getFullName();
    }

    public function getClipboardBackup(): string
    {
        $block = [
            'type' => 'courseware-blocks',
            'id' => $this->id,
            'attributes' => [
                'position'=> $this->position,
                'block-type'=> $this->type->getType(),
                'title'=> $this->type->getTitle(),
                'visible'=> $this->visible,
                'commentable' => $this->commentable,
                'payload'=> $this->type->getPayload(),
                'mkdate'=> $this->mkdate,
                'chdate'=> $this->chdate
            ]
        ];

        return json_encode($block);
    }

    /**
     * Copies this block into another container such that the given user is the owner of the copy.
     *
     * @param User      $user      the owner and editor of the new copy of this block
     * @param Container $container the container this block will be copied into
     *
     * @return Block the copy of this block
     */
    public function copy(User $user, Container $container, $sectionIndex = null): Block
    {
        /** @var StructuralElement $struct */
        $struct = $container->structural_element;
        $rangeId = $struct->getRangeId();

        $block = self::create([
            'container_id' => $container->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'position' => $container->countBlocks(),
            'block_type' => $this->type->getType(),
            'payload' => json_encode($this->type->copyPayload($rangeId)),
            'visible' => 1,
            'commentable' => 0
        ]);

        //update Container payload
        $container->type->addBlock($block, $sectionIndex);
        $container->store();

        return $block;
    }

    public static function createFromData(User $user, $data, Container $container, $sectionIndex = null): Block
    {
        $struct = $container->structural_element;
        $rangeId = $struct->getRangeId();

        $block = self::build([
            'container_id' => $container->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'position' => $container->countBlocks(),
            'block_type' => $data->attributes->{'block-type'},
            'payload' => json_encode($data->attributes->payload),
            'visible' => 1,
            'commentable' => 0
        ]);

        $block->payload = json_encode($block->type->copyPayload($rangeId));
        $block->store();

        //update Container payload
        $container->type->addBlock($block, $sectionIndex);
        $container->store();

        return $block;
    }

    public function getBlockType(): ?string
    {
        if ($this->type->findBlockType($this->block_type)) {
            return $this->block_type;
        } else {
            $this->payload = json_encode(array(
                'original_block_type' => $this->block_type
            ));
            return 'error';
        }
    }

    public function getStructuralElement(): ?StructuralElement
    {
        $sql = 'SELECT se.*
                FROM cw_blocks b
                JOIN cw_containers c ON c.id = b.container_id
                JOIN cw_structural_elements se ON se.id = c.structural_element_id
                WHERE  b.id = ?';
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
        $blocks = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_blocks WHERE owner_id = ? OR editor_id = ?',
            [$storage->user_id, $storage->user_id]
        );
        if ($blocks) {
            $storage->addTabularData(_('Courseware Blöcke'), 'cw_blocks', $blocks);
        }
        
    }
}
