<?php

namespace Courseware;

use User;

/**
 * Courseware's containers.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int                                      $id                    database column
 * @property int                                      $structural_element_id database column
 * @property string                                   $owner_id              database column
 * @property string                                   $editor_id             database column
 * @property string                                   $edit_blocker_id       database column
 * @property int                                      $position              database column
 * @property int                                      $site                  database column
 * @property string                                   $container_type        database column
 * @property int                                      $visible               database column
 * @property string                                   $payload               database column
 * @property int                                      $mkdate                database column
 * @property int                                      $chdate                database column
 * @property \Courseware\ContainerTypes\ContainerType $type                  computed column read/write
 * @property \SimpleORMapCollection                   $blocks                has_many Courseware\Block
 * @property \User                                    $owner                 belongs_to User
 * @property \User                                    $editor                belongs_to User
 * @property \User                                    $edit_blocker          belongs_to User
 * @property \Courseware\StructuralElement            $structural_element    belongs_to Courseware\StructuralElement
 */
class Container extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_containers';

        $config['serialized_fields']['payload'] = 'JSONArrayObject';

        $config['has_many']['blocks'] = [
            'class_name' => Block::class,
            'assoc_foreign_key' => 'container_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY position',
        ];

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

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        $config['additional_fields']['type'] = [
            'get' => function ($container) {
                return ContainerTypes\ContainerType::factory($container);
            },
            'set' => false,
        ];

        parent::configure($config);
    }

    /**
     * Returns the structural element this container belongs to.
     *
     * @return StructuralElement the structural element
     */
    public function getStructuralElement(): StructuralElement
    {
        return $this->structural_element;
    }

    /**
     * Returns the number of blocks contained in this.
     *
     * @return int the number of blocks contained in this
     */
    public function countBlocks(): int
    {
        return Block::countBySql('container_id = ?', [$this->id]);
    }

    public function getClipboardBackup(): string
    {
        $container = [
            'type' => 'courseware-containers',
            'id' => $this->id,
            'attributes' => [
                'position' => $this->position,
                'site' => $this->site,
                'container-type' => $this->type->getType(),
                'title' => $this->type->getTitle(),
                'visible' => $this->visible,
                'payload' => $this->type->getPayload(),
                'mkdate' => $this->mkdate,
                'chdate' => $this->chdate
            ],
            'blocks' => $this->getClipboardBackupBlocks()
        ];
        return json_encode($container, true);
    }

    public function getClipboardBackupBlocks(): array
    {
        return $this->blocks->map(function (Block $block) {
            return json_decode($block->getClipboardBackup());
        });
    }

    /**
     * Copies this block into another structural element such that the given user is the owner of the copy.
     *
     * @param User              $user    the owner and editor of the new copy of this block
     * @param StructuralElement $element the structural element this block will be copied into
     *
     * @return array an array containing the container object and the block maps
     */
    public function copy(User $user, StructuralElement $element): array
    {
        $container = self::create([
            'structural_element_id' => $element->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'position' => $element->countContainers(),
            'container_type' => $this->type->getType(),
            'payload' => $this['payload'],
        ]);

        list($blockMapIds, $blockMapObjs) = $this->copyBlocks($user, $container);

        $container['payload'] = $container->type->copyPayload($blockMapIds);

        $container->store();

        return [$container, $blockMapObjs];
    }

    private function copyBlocks(User $user, Container $newContainer): array
    {
        $blockMap = [];
        $newBlockList = [];

        foreach ($this->blocks as $block) {
            $newBlock = $block->copy($user, $newContainer);
            $blockMap[$block->id] = $newBlock->id;
            $newBlockList[$block->id] = $newBlock;
        }

        return [$blockMap, $newBlockList];
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $containers = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_containers WHERE owner_id = ? OR editor_id = ?',
            [$storage->user_id, $storage->user_id]
        );
        if ($containers) {
            $storage->addTabularData(_('Courseware Abschnitte'), 'cw_containers', $containers);
        }
        
    }

    public static function createFromData(User $user, $data, StructuralElement $element): Container
    {
        $container = self::create([
            'structural_element_id' => $element->id,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'position' => $element->countContainers(),
            'container_type' => $data->attributes->{'container-type'},
            'payload' => json_encode($data->attributes->payload),
        ]);

        $blockMap = self::createBlocksFromData($user, $container, $data);
        $container['payload'] = $container->type->copyPayload($blockMap);
        $container->store();

        return $container;
    }

    private static function createBlocksFromData($user, $container, $data): array
    {
        $blockMap = [];

        foreach ($data->blocks as $block) {
            $newBlock = Block::createFromData($user, $block, $container);
            $blockMap[$block->id] = $newBlock->id;
        }

        return $blockMap;
    }
}
