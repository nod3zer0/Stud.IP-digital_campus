<?php

namespace Courseware;

/**
 * This class represents an instance of a courseware of a course or a user.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Instance
{
    public static function deleteForRange(\Range $range): void
    {
        $root = null;
        switch ($range->getRangeType()) {
            case 'course':
                $root = StructuralElement::getCoursewareCourse($range->getRangeId());
                break;
            case 'user':
                $root = StructuralElement::getCoursewareUser($range->getRangeId());
                break;
            default:
                throw new \InvalidArgumentException('Only ranges of type "user" and "course" are currently supported.');
        }

        // there is no courseware for this course
        if (!$root) {
            return;
        }

        $instance = new self($root);

        $range->getConfiguration()->delete('COURSEWARE_SEQUENTIAL_PROGRESSION');
        $range->getConfiguration()->delete('COURSEWARE_EDITING_PERMISSION');

        $last_element_configs = \ConfigValue::findBySQL('field = ? AND value LIKE ?', [
            'COURSEWARE_LAST_ELEMENT',
            '%' . $range->getRangeId() . '%',
        ]);
        foreach ($last_element_configs as $config) {
            $arr = json_decode($config->value, true);
            $arr = array_filter(
                $arr,
                function ($key) use ($range) {
                    return $key !== $range->id;
                },
                ARRAY_FILTER_USE_KEY
            );
            \UserConfig::get($config->range_id)->unsetValue('COURSEWARE_LAST_ELEMENT');
            \UserConfig::get($config->range_id)->store('COURSEWARE_LAST_ELEMENT', $arr);
        }

        $root->delete();
    }

    /**
     * @var StructuralElement
     */
    private $root;

    /**
     * Create a new representation of a a courseware instance.
     *
     * This model class purely represents and does not create anything. Its purpose is to have all things related to a
     * single courseware instance in one place.
     *
     * @param StructuralElement $root the root of this courseware instance
     */
    public function __construct(StructuralElement $root)
    {
        $this->root = $root;
    }

    /**
     * Returns the root element of this courseware instance.
     *
     * @return StructuralElement the root element of this courseware instance
     */
    public function getRoot(): StructuralElement
    {
        return $this->root;
    }

    /**
     * Returns the range this courseware instance belongs to.
     *
     * @return \Range the range this courseware instance belongs to
     */
    public function getRange(): \Range
    {
        $rangeType = $this->root['range_type'];

        return $this->root->$rangeType;
    }

    /**
     * Returns the type of this courseware instance's range as coded in the root element.
     *
     * @return string the type of this courseware instance's range
     */
    public function getRangeType(): string
    {
        return $this->root['range_type'];
    }

    /**
     * Returns all associated block types registered to this courseware instance.
     *
     * @return array a list of all associated block types
     */
    public function getBlockTypes(): array
    {
        $types = BlockTypes\BlockType::getBlockTypes();

        return $types;
    }

    /**
     * Returns all associated container types registered to this courseware instance.
     *
     * @return array a list of all associated block types
     */
    public function getContainerTypes(): array
    {
        $types = ContainerTypes\ContainerType::getContainerTypes();

        return $types;
    }

    /**
     * Returns a user's favorite block types for this instance.
     *
     * @param \User $user the user for whom the favorite block types will be returned
     *
     * @return array a list of favorite block types
     */
    public function getFavoriteBlockTypes(\User $user): array
    {
        /** @var array $favoriteBlockTypes */
        $favoriteBlockTypes = \UserConfig::get($user->id)->getValue('COURSEWARE_FAVORITE_BLOCK_TYPES');

        return $favoriteBlockTypes;
    }

    /**
     * Sets a user's favorite block types for this courseware instance.
     *
     * @param \User $user      the user for whom the favorite block types will be set
     * @param array $favorites the list of favorite block types
     */
    public function setFavoriteBlockTypes(\User $user, array $favorites): void
    {
        \UserConfig::get($user->id)->store('COURSEWARE_FAVORITE_BLOCK_TYPES', $favorites);
    }

    /**
     * Returns whether this courseware instance uses a sequential progression through the structural elements.
     *
     * @return bool true if this courseware instance uses a sequential progression, false otherwise
     */
    public function getSequentialProgression(): bool
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        $sequentialProgression = $range->getConfiguration()->COURSEWARE_SEQUENTIAL_PROGRESSION[$root->id];

        return (bool) $sequentialProgression;
    }

    /**
     * Sets whether this courseware instance uses a sequential progression through the structural elements.
     *
     * @param bool $isSequentialProgression true if this courseware instance uses a sequential progression
     */
    public function setSequentialProgression(bool $isSequentialProgression): void
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        $progressions = $range->getConfiguration()->getValue('COURSEWARE_SEQUENTIAL_PROGRESSION');
        $progressions[$root->id] = $isSequentialProgression ? 1 : 0;
        $range->getConfiguration()->store('COURSEWARE_SEQUENTIAL_PROGRESSION', $progressions);
    }

    const EDITING_PERMISSION_DOZENT = 'dozent';
    const EDITING_PERMISSION_TUTOR = 'tutor';

    /**
     * Returns the level needed to edit this courseware instance.
     *
     * @return string can be either `Instance::EDITING_PERMISSION_DOZENT` or  `Instance::EDITING_PERMISSION_TUTOR`
     */
    public function getEditingPermissionLevel(): string
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        /** @var string $editingPermissionLevel */
        $editingPermissionLevel = $range->getConfiguration()->COURSEWARE_EDITING_PERMISSION[$root->id];
        if ($editingPermissionLevel) {
            $this->validateEditingPermissionLevel($editingPermissionLevel);
            return $editingPermissionLevel;
        }

        return self::EDITING_PERMISSION_TUTOR; // tutor is default
    }

    /**
     * Sets the level needed to edit this courseware instance.
     *
     * @param string $editingPermissionLevel can be either `Instance::EDITING_PERMISSION_DOZENT` or
     *                                       `Instance::EDITING_PERMISSION_TUTOR`
     */
    public function setEditingPermissionLevel(string $editingPermissionLevel): void
    {
        $this->validateEditingPermissionLevel($editingPermissionLevel);
        $range = $this->getRange();
        $root = $this->getRoot();
        $permissions = $range->getConfiguration()->getValue('COURSEWARE_EDITING_PERMISSION');
        $permissions[$root->id] = $editingPermissionLevel;
        $range->getConfiguration()->store('COURSEWARE_EDITING_PERMISSION', $permissions);
    }

    /**
     * Validates a editing permission level.
     *
     * @param string $editingPermissionLevel the editing permission level to validate
     *
     * @return bool true if this editing permission level is valid, false otherwise
     */
    public function isValidEditingPermissionLevel(string $editingPermissionLevel): bool
    {
        return in_array($editingPermissionLevel, [self::EDITING_PERMISSION_DOZENT, self::EDITING_PERMISSION_TUTOR]);
    }

    private function validateEditingPermissionLevel(string $editingPermissionLevel): void
    {
        if (!$this->isValidEditingPermissionLevel($editingPermissionLevel)) {
            throw new \InvalidArgumentException('Invalid editing permission of courseware.');
        }
    }


    /**
     * Returns the certificate creation settings.
     *
     * @return array
     */
    public function getCertificateSettings(): array
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        /** @var array $certificateSettings */
        $certificateSettings = json_decode(
            $range->getConfiguration()->COURSEWARE_CERTIFICATE_SETTINGS[$root->id],
            true
        )?: [];
        $this->validateCertificateSettings($certificateSettings);

        return $certificateSettings;
    }

    /**
     * Sets the certificate settings for this courseware instance.
     *
     * @param array $certificateSettings an array of parameters
     */
    public function setCertificateSettings(array $certificateSettings): void
    {
        $this->validateCertificateSettings($certificateSettings);
        $range = $this->getRange();
        $root = $this->getRoot();
        $settings = $range->getConfiguration()->getValue('COURSEWARE_CERTIFICATE_SETTINGS');
        $settings[$root->id] = count($certificateSettings) > 0 ? json_encode($certificateSettings) : null;
        $range->getConfiguration()->store('COURSEWARE_CERTIFICATE_SETTINGS', $settings);
    }

    /**
     * Validates certificate settings.
     *
     * @param array $certificateSettings settings for certificate creation
     *
     * @return bool true if all given values are valid, false otherwise
     */
    public function isValidCertificateSettings(array $certificateSettings): bool
    {
        return (int) $certificateSettings['threshold'] >= 0 && (int) $certificateSettings['threshold'] <= 100;
    }

    private function validateCertificateSettings(array $certificateSettings): void
    {
        if (!$this->isValidCertificateSettings($certificateSettings)) {
            throw new \InvalidArgumentException('Invalid certificate settings given.');
        }
    }

    /**
     * Returns the reminder message sending settings.
     *
     * @return array
     */
    public function getReminderSettings(): array
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        /** @var int $reminderInterval */
        $reminderSettings = json_decode(
            $range->getConfiguration()->COURSEWARE_REMINDER_SETTINGS[$root->id],
            true
        )?: [];
        $this->validateReminderSettings($reminderSettings);

        return $reminderSettings;
    }

    /**
     * Sets the reminder message settings this courseware instance.
     *
     * @param array $reminderSettings an array of parameters
     */
    public function setReminderSettings(array $reminderSettings): void
    {
        $this->validateReminderSettings($reminderSettings);
        $range = $this->getRange();
        $root = $this->getRoot();
        $settings = $range->getConfiguration()->getValue('COURSEWARE_REMINDER_SETTINGS');
        $settings[$root->id] = count($reminderSettings) > 0 ? json_encode($reminderSettings) : null;
        $range->getConfiguration()->store('COURSEWARE_REMINDER_SETTINGS', $settings);
    }

    /**
     * Validates reminder message settings.
     *
     * @param array $reminderSettings settings for reminder mail sending
     *
     * @return bool true if all given values are valid, false otherwise
     */
    public function isValidReminderSettings(array $reminderSettings): bool
    {
        $valid = in_array($reminderSettings['interval'], [0, 7, 14, 30, 90, 180, 365]);

        return $valid;
    }

    private function validateReminderSettings(array $reminderSettings): void
    {
        if (!$this->isValidReminderSettings($reminderSettings)) {
            throw new \InvalidArgumentException('Invalid reminder settings given.');
        }
    }

    /**
     * Returns the progress resetting settings.
     *
     * @return array
     */
    public function getResetProgressSettings(): array
    {
        $range = $this->getRange();
        $root = $this->getRoot();
        /** @var int $reminderInterval */
        $resetProgressSettings = json_decode(
            $range->getConfiguration()->COURSEWARE_RESET_PROGRESS_SETTINGS[$root->id],
            true
        )?: [];
        $this->validateResetProgressSettings($resetProgressSettings);

        return $resetProgressSettings;
    }

    /**
     * Sets the progress resetting settings this courseware instance.
     *
     * @param array $reminderSettings an array of parameters
     */
    public function setResetProgressSettings(array $resetProgressSettings): void
    {
        $this->validateResetProgressSettings($resetProgressSettings);
        $range = $this->getRange();
        $root = $this->getRoot();
        $settings = $range->getConfiguration()->getValue('COURSEWARE_RESET_PROGRESS_SETTINGS');
        $settings[$root->id] = count($resetProgressSettings) > 0 ? json_encode($resetProgressSettings) : null;
        $range->getConfiguration()->store('COURSEWARE_RESET_PROGRESS_SETTINGS', $settings);
    }

    /**
     * Validates progress resetting settings.
     *
     * @param array $resetProgressSettings settings for progress resetting
     *
     * @return bool true if all given values are valid, false otherwise
     */
    public function isValidResetProgressSettings(array $resetProgressSettings): bool
    {
        $valid = in_array($resetProgressSettings['interval'], [0, 14, 30, 90, 180, 365]);

        return $valid;
    }

    private function validateResetProgressSettings(array $resetProgressSettings): void
    {
        if (!$this->isValidResetProgressSettings($resetProgressSettings)) {
            throw new \InvalidArgumentException('Invalid progress resetting settings given.');
        }
    }

    /**
     * Returns all bookmarks of a user associated to this courseware instance.
     *
     * @param \User $user the user for whom to find associated bookmarks for
     *
     * @return array a list of the given user's bookmarks associated to this instance
     */
    public function getUsersBookmarks(\User $user): array
    {
        return StructuralElement::findUsersBookmarksByRange($user, $this->getRange());
    }

    public function findAllStructuralElements(): iterable
    {
        $sql = 'SELECT se.*
                FROM cw_structural_elements se
                WHERE se.range_id = ? AND se.range_type = ?';
        $statement = \DBManager::get()->prepare($sql);
        $statement->execute([$this->root['range_id'], $this->root['range_type']]);

        $data = [];
        foreach ($statement as $key => $row) {
            $data[] = \Courseware\StructuralElement::build($row, false);
        }

        return $data;
    }

    public function findAllBlocks(): iterable
    {
        $sql = 'SELECT b.*
                FROM cw_structural_elements se
                JOIN cw_containers c ON se.id = c.structural_element_id
                JOIN cw_blocks b ON c.id = b.container_id
                WHERE se.range_id = ? AND se.range_type = ?';
        $statement = \DBManager::get()->prepare($sql);
        $statement->execute([$this->root['range_id'], $this->root['range_type']]);

        $data = [];
        foreach ($statement as $key => $row) {
            $data[] = \Courseware\Block::build($row, false);
        }

        return $data;
    }

    /**
     * Find all blocks of this instance and group them by their structural element's ID.
     * You may specify your own `$formatter` instead of the default one which stores the blocks as instances of \Courseware\Block.
     *
     * @param ?callable(array $row): mixed $formatter Provide your own callable if you need something else instead of
     *                                                full-blown instances of \Courseware\Block.
     * @return iterable all the (optionally formatted) blocks grouped by the IDs of the structural element containing
     *                  that block.
     */
    public function findAllBlocksGroupedByStructuralElementId(callable $formatter = null): iterable
    {
        if (!$formatter) {
            $formatter = function ($row) {
                return \Courseware\Block::build($row, false);
            };
        }

        $sql = 'SELECT se.id AS structural_element_id, b.*
                FROM cw_structural_elements se
                JOIN cw_containers c ON se.id = c.structural_element_id
                JOIN cw_blocks b ON c.id = b.container_id
                WHERE se.range_id = ? AND se.range_type = ?';

        $statement = \DBManager::get()->prepare($sql);
        $statement->execute([$this->root['range_id'], $this->root['range_type']]);

        $data = [];
        foreach ($statement as $row) {
            $structuralElementId = $row['structural_element_id'];
            unset($row['structural_element_id']);

            if (!isset($data[$structuralElementId])) {
                $data[$structuralElementId] = [];
            }
            $data[$structuralElementId][] = $formatter($row);
        }

        return $data;
    }
}
