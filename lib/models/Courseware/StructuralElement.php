<?php

namespace Courseware;

use User;

/**
 * Courseware's structural elements.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @property int                            $id                 database column
 * @property int                            $parent_id          database column
 * @property int                            $is_link            database column
 * @property int                            $target_id          database column
 * @property string                         $range_id           database column
 * @property string                         $range_type         database column
 * @property string                         $owner_id           database column
 * @property string                         $editor_id          database column
 * @property string                         $edit_blocker_id    database column
 * @property int                            $position           database column
 * @property string                         $title              database column
 * @property string                         $image_id           database column
 * @property string                         $purpose            database column
 * @property \JSONArrayObject               $payload            database column
 * @property int                            $public             database column
 * @property string                         $release_date       database column
 * @property string                         $withdraw_date      database column
 * @property \JSONArrayObject               $read_approval      database column
 * @property \JSONArrayObject               $write_approval     database column
 * @property \JSONArrayObject               $copy_approval      database column
 * @property \JSONArrayObject               $external_relations database column
 * @property int                            $mkdate             database column
 * @property int                            $chdate             database column
 * @property \SimpleORMapCollection         $children           has_many Courseware\StructuralElement
 * @property \SimpleORMapCollection         $containers         has_many Courseware\Container
 * @property ?\Courseware\StructuralElement $parent             belongs_to Courseware\StructuralElement
 * @property \User                          $user               belongs_to User
 * @property \Course                        $course             belongs_to Course
 * @property \User                          $owner              belongs_to User
 * @property \User                          $editor             belongs_to User
 * @property ?\User                         $edit_blocker       belongs_to User
 * @property ?\FileRef                      $image              has_one FileRef
 * @property ?\Courseware\Task              $task               has_one Courseware\Task
 * @property \SimpleORMapCollection         $comments           has_many Courseware\StructuralElementComment
 * @property \SimpleORMapCollection         $feedback           has_many Courseware\StructuralElementFeedback
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class StructuralElement extends \SimpleORMap implements \PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_structural_elements';

        $config['serialized_fields']['payload'] = 'JSONArrayObject';
        $config['serialized_fields']['read_approval'] = 'JSONArrayObject';
        $config['serialized_fields']['write_approval'] = 'JSONArrayObject';
        $config['serialized_fields']['copy_approval'] = 'JSONArrayObject';
        $config['serialized_fields']['external_relations'] = 'JSONArrayObject';

        $config['has_many']['children'] = [
            'class_name' => StructuralElement::class,
            'assoc_foreign_key' => 'parent_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY position',
        ];

        $config['has_many']['containers'] = [
            'class_name' => Container::class,
            'assoc_foreign_key' => 'structural_element_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY position',
        ];

        $config['has_one']['task'] = [
            'class_name' => Task::class,
            'assoc_foreign_key' => 'structural_element_id',
            'on_delete' => 'delete',
        ];

        $config['belongs_to']['parent'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'parent_id',
        ];

        $config['belongs_to']['user'] = [
            'class_name' => \User::class,
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'user_id',
        ];

        $config['belongs_to']['course'] = [
            'class_name' => \Course::class,
            'foreign_key' => 'range_id',
            'assoc_foreign_key' => 'seminar_id',
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

        $config['has_one']['image'] = [
            'class_name' => \FileRef::class,
            'foreign_key' => 'image_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
        ];

        $config['has_many']['comments'] = [
            'class_name' => StructuralElementComment::class,
            'assoc_foreign_key' => 'structural_element_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        $config['has_many']['feedback'] = [
            'class_name' => StructuralElementFeedback::class,
            'assoc_foreign_key' => 'structural_element_id',
            'on_delete' => 'delete',
            'on_store' => 'store',
            'order_by' => 'ORDER BY chdate',
        ];

        parent::configure($config);
    }

    /**
     * Returns the root element of a courseware instance for a user.
     *
     * @param string $userId the user's id to return the root element for
     *
     * @return ?StructuralElement null, if there is none, the root StructuralElement if there is one
     */
    public static function getCoursewareUser(string $userId): ?StructuralElement
    {
        return self::getCourseware($userId, 'user');
    }

    /**
     * Returns the root element of a courseware instance for a course.
     *
     * @param string $courseId the course's id to return the root element for
     *
     * @return ?StructuralElement null, if there is none, the root StructuralElement if there is one
     */
    public static function getCoursewareCourse(string $courseId): ?StructuralElement
    {
        return self::getCourseware($courseId, 'course');
    }

    public static function getSharedCoursewareUser(string $root_id): ?StructuralElement
    {
        return self::getCourseware('', '', $root_id);
    }

    private static function getCourseware(string $rangeId, string $rangeType, string $root_id = null): ?StructuralElement
    {
        if ($root_id) {
            $result = self::find($root_id);
        } else {
            $result = self::findOneBySQL(
                'range_id = ?
                AND range_type = ? AND parent_id IS NULL',
                [$rangeId, $rangeType]
            );
        }

        return $result;
    }

    /**
     * Returns the ID of the associated range.
     *
     * @return string the id of the range
     */
    public function getRangeId(): string
    {
        return $this->range_id;
    }

    /**
     * @return bool true, if this object is the root of a courseware, false otherwise
     */
    public function isRootNode(): bool
    {
        return null === $this->parent_id;
    }

    /**
     * @return bool true, if this object purpose is task, false otherwise
     */
    public function isTask(): bool
    {
        return $this->purpose === 'task';
    }

    /**
     * @param User $user the user to validate
     *
     * @return bool true if the user may edit this instance
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function canEdit($user): bool
    {
        if ($GLOBALS['perm']->have_perm('root', $user->id)) {
            return true;
        }

        switch ($this->range_type) {
            case 'user':
                if ($this->range_id === $user->id) {
                    return true;
                }

                return $this->hasWriteApproval($user);

            case 'course':
                $hasEditingPermission = $this->hasEditingPermission($user);
                if ($this->isTask()) {
                    $task = $this->task;
                    if (!$task) {
                        $task = $this->findParentTask();
                        if (!$task) {
                            return false;
                        }
                    }

                    if ($hasEditingPermission) {
                        return false;
                    }

                    if ($task->isSubmitted()) {
                        return false;
                    }

                    return $task->userIsASolver($user);
                }

                if ($hasEditingPermission) {
                    return true;
                }

                return $this->hasWriteApproval($user);

            default:
                throw new \InvalidArgumentException('Unknown range type.');
        }
    }

    /**
     * @param mixed $user the user to validate
     *
     * @return bool true if the user may read this instance
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function canRead($user): bool
    {
        // root darf immer
        if ($GLOBALS['perm']->have_perm('root', $user->id)) {
            return true;
        }

        switch ($this->range_type) {
            case 'user':
                if ($this->range_id === $user->id) {
                    return true;
                }

                $link = StructuralElement::findOneBySQL('target_id = ?', [$this->id]);
                if ($link) {
                    return true;
                }

                return $this->hasReadApproval($user);
            case 'course':
                if (!$GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user->id)) {
                    return false;
                }

                if ($this->canEdit($user)) {
                    return true;
                }

                if (!$this->releasedForReaders($this)) {
                    return false;
                }

                return $this->hasReadApproval($user);

            default:
                throw new \InvalidArgumentException('Unknown range type.');
        }
    }

    public function canVisit($user): bool
    {
        // root darf immer
        if ($GLOBALS['perm']->have_perm('root', $user->id)) {
            return true;
        }

        switch ($this->range_type) {
            case 'user':
                if ($this->range_id === $user->id) {
                    return true;
                }

                return $this->hasReadApproval($user);

            case 'course':
                if (!$GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user->id)) {
                    return false;
                }

                if ($this->isTask()) {
                    $task = $this->task;
                    if (!$task) {
                        $task = $this->findParentTask();
                        if (!$task) {
                            return false;
                        }
                    }

                    if ($task->isSubmitted() && $this->hasEditingPermission($user)) {
                        return true;
                    }

                    return $task->userIsASolver($user);
                }

                if ($this->canEdit($user)) {
                    return true;
                }

                if (!$this->releasedForReaders($this)) {
                    return false;
                }

                return $this->hasReadApproval($user) && $this->canReadSequential($user);

            default:
                throw new \InvalidArgumentException('Unknown range type.');
        }
    }

    /**
     * @param \User|\Seminar_User $user
     */
    public function hasEditingPermission($user): bool
    {
        $unit = $this->findUnit();
        return $GLOBALS['perm']->have_perm('root', $user->id)
            || $GLOBALS['perm']->have_studip_perm(
                $unit->config['editing_permission'],
                $this->range_id,
                $user->id
            );
    }

    private function hasReadApproval($user): bool
    {
        // this property is shared between all range types.
        if ($this->read_approval['all']) {
            return true;
        }

        // now we also check against the perms for contents.
        if ($this->range_type === 'user') {
            return $this->hasUserReadApproval($user);
        } else {
            if (!count($this->read_approval)) {
                return true;
            }

            // find user in users
            $users = $this->read_approval['users'];
            foreach ($users as $approvedUserId) {
                if ($approvedUserId === $user->id) {
                    return true;
                }
            }

            // find user in groups
            $groups = $this->read_approval['groups'];
            foreach ($groups as $groupId) {
                /** @var ?\Statusgruppen $group */
                $group = \Statusgruppen::find($groupId);
                if ($group && $group->isMember($user->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasUserReadApproval($user): bool
    {
        if (!count($this->read_approval)) {
            if ($this->isRootNode()) {
                return false;
            }
            return $this->parent->hasUserReadApproval($user);
        }

        // find user in users
        $users = $this->read_approval['users'];
        foreach ($users as $listedUserPerm) {
            // now for contents, there is an expiry date defined.
            if (!empty($listedUserPerm['expiry']) && strtotime($listedUserPerm['expiry']) < strtotime('today')) {
                if ($this->isRootNode()) {
                    return false;
                }
                return $this->parent->hasUserReadApproval($user);
            }
            // In order to have a record of the users in the perms list of contents,
            // we keep a full perm record in read_approval column, and set read property to true or false,
            // this won't apply to write_approval column.
            if ($listedUserPerm['id'] == $user->id && $listedUserPerm['read'] == true) {
                return true;
            }
        }
        //User not found.
        return false;
    }

    private function hasWriteApproval($user): bool
    {
        // this property is shared between all range types.
        if ($this->write_approval['all']) {
            return true;
        }

        // now we also check against the perms for contents.
        if ($this->range_type === 'user') {
            return $this->hasUserWriteApproval($user);
        } else {
            if (!count($this->write_approval)) {
                return false;
            }

            if ($this->write_approval['all']) {
                return true;
            }

            // find user in users
            $users = $this->write_approval['users']->getArrayCopy();
            if (in_array($user->id, $users)) {
                return true;
            }

            // find user in groups
            foreach (\Statusgruppen::findMany($this->write_approval['groups']->getArrayCopy()) as $group) {
                if ($group->isMember($user->id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasUserWriteApproval($user): bool
    {
        if (!count($this->write_approval)) {
            if ($this->isRootNode()) {
                return false;
            }
            return $this->parent->hasUserWriteApproval($user);
        }

        // find user in users
        $users = $this->write_approval['users'];
        foreach ($users as $listedUserPerm) {
            // now for contents, there is an expiry date defined.
            if (!empty($listedUserPerm['expiry']) && strtotime($listedUserPerm['expiry']) < strtotime('today')) {
                if ($this->isRootNode()) {
                    return false;
                }
                return $this->parent->hasUserWriteApproval($user);
            }
            if ($listedUserPerm['id'] == $user->id) {
                return true;
            }
        }

        if ($this->isRootNode()) {
            return false;
        }
        return $this->parent->hasUserWriteApproval($user);
    }

    /**
     * @param mixed $user the user to validate
     *
     * @return bool true if the user may read this instance in sequential progression
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function canReadSequential($user): bool
    {
        $unit = $this->findUnit();
        if (!$unit->config['sequential_progression']) {
            return true;
        }

        return $this->previousProgressAchieved($user);
    }

    /**
     * @return bool true if the user may read this instance in time interval
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function releasedForReaders(StructuralElement $element): bool
    {
        $released = false;
        if (!$element->release_date || $element->release_date <= time()) {
            $released = true;
        }

        if ($element->withdraw_date && $element->withdraw_date <= time()) {
            $released = false;
        }

        $parent_released = true;
        if (!$element->isRootNode()) {
            $parent_released = $this->releasedForReaders($element->parent);
        }

        return $released && $parent_released;
    }

    /**
     * @param mixed $user the user to validate
     *
     * @return bool true if the user has achieved previous elements
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function previousProgressAchieved($user): bool
    {
        $elements = $this->findCoursewareElements($user);

        foreach ($elements as $element) {
            // found me in depth-first order
            // so everything before me was fine and we're done
            if ($element->id == $this->id) {
                break;
            }

            if (!$element->hasBeenAchieved($user)) {
                return false;
            }
        }

        return true;
    }

    private function findCoursewareElements($user): array
    {
        $root = $this->getCourseware($this->range_id, $this->range_type);
        $elements = array_merge([$root], $root->findDescendants($user));

        return $elements;
    }

    private function hasBeenAchieved($user): bool
    {
        foreach ($this->containers as $container) {
            foreach ($container->blocks as $block) {
                /** @var ?UserProgress $progress */
                $progress = UserProgress::findOneBySQL('user_id = ? and block_id = ?', [$user->id, $block->id]);

                if (!$progress || $progress->grade != 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Returns all projects of a user and a given purpose.
     *
     * @param string $userId  the ID of the user
     * @param string $purpose a string containing the purpose of the projects
     *
     * @return StructuralElement[] a list of projects
     */
    public static function findProjects(string $userId, string $purpose = 'all'): array
    {
        $root = self::getCoursewareUser($userId);
        if ('all' == $purpose) {
            return self::findBySQL('range_id = ? AND parent_id = ? ORDER BY position ASC', [$userId, $root->id]);
        } else {
            return self::findBySQL('range_id = ? AND parent_id = ? AND purpose = ? ORDER BY position ASC', [
                $userId,
                $root->id,
                $purpose,
            ]);
        }
    }

    /**
     * Return the number of children of this instance.
     *
     * @return int the number of children
     */
    public function countChildren(): int
    {
        return self::countBySQL('parent_id= ? ', [$this->id]);
    }

    /**
     * Returns the list of all ancestors traversing up to the root.
     *
     * @return array a list of all ancestors of this instance up to the root
     */
    public function findAncestors(): array
    {
        $ancestors = [];

        if ($this->parent) {
            $ancestors[] = $this->parent;
            $ancestors = array_merge($this->parent->findAncestors(), $ancestors);
        }

        return $ancestors;
    }

    public function findUnit()
    {
        if ($this->isRootNode()) {
            $root = $this;
        } else {
            $root = $this->findAncestors()[0];
        }

        return Unit::findOneBySQL('structural_element_id = ?', [$root->id]);
    }

    /**
     * Returns the list of all descendants of this instance in depth-first search order.
     *
     * @param ?User  $user  the user whose bookmarked structural elements will be returned
     *
     * @return StructuralElement[] a list of all descendants
     */
    public function findDescendants(User $user = null)
    {
        $descendants = [];
        foreach ($this->children as $child) {
            if ($user === null || $child->canRead($user)) {
                $descendants[] = $child;
                $descendants = array_merge($descendants, $child->findDescendants($user));
            }
        }

        return $descendants;
    }

    /**
     * Creates a new and empty courseware instance for a given range.
     *
     * @param string $rangeId   the ID of the range
     * @param string $rangeType the type of the range
     *
     * @return Instance the created courseware instance
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function createEmptyCourseware(string $rangeId, string $rangeType): Instance
    {
        if ('user' == $rangeType) {
            $user = \User::find($rangeId);
        } else {
            /** @var ?\Course $course */
            $course = \Course::find($rangeId);
            /** @var ?\User $user */
            $user = \User::find($GLOBALS['user']->id); //must be dozent
            if ('dozent' != $course->getParticipantStatus($user->id)) {
                $coursemembers = $course->getMembersWithStatus('dozent'); //get studip perm
                $user = $coursemembers[0]->user;
            }
        }

        $struct = self::build([
            'parent_id' => null,
            'range_id' => $rangeId,
            'range_type' => $rangeType,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'title' => _('neue Seite'),
        ]);

        $struct->store();

        return new Instance($struct);
    }

    /**
     * Counts and returns the number of containers in this structural element.
     *
     * @return int the number of containers of this structural element
     */
    public function countContainers(): int
    {
        return Container::countBySql('structural_element_id = ?', [$this->id]);
    }

    /**
     * Returns all structural elements that a user bookmarked in a range.
     *
     * @param User   $user  the user whose bookmarked structural elements will be returned
     * @param \Range $range the range in which the user bookmarked structural elements
     *
     * @return array a list of bookmarked structural elements
     */
    public static function findUsersBookmarksByRange(User $user, \Range $range): array
    {
        if (!in_array($range->getRangeType(), ['course', 'user'])) {
            throw new \InvalidArgumentException();
        }

        $sql = <<<'SQL'
            SELECT s.* FROM cw_structural_elements s
            JOIN cw_bookmarks b
            ON s.id = b.element_id
            WHERE s.range_id = ? AND s.range_type = ? AND b.user_id = ?
            ORDER BY b.chdate DESC
SQL;
        $params = [$range->getRangeId(), $range->getRangeType(), $user->id];

        return \DBManager::get()->fetchAll($sql, $params, StructuralElement::class . '::buildExisting');
    }

    /**
     * Returns the URL of the image associated to this structural element.
     *
     * @return string the image URL, if it exists; an empty string otherwise
     */
    public function getImageUrl()
    {
        return $this->image ? $this->image->getDownloadURL() : null;
    }

    /**
     * Copies this instance into another course oder users contents.
     *
     * @param User              $user   this user will be the owner of the copy
     * @param Range $parent the target where to copy this instance
     *
     * @return StructuralElement the copy of this instance
     */
    public function copyToRange(User $user, string $rangeId, string $rangeType, string $purpose = ''): StructuralElement
    {
        $element = self::build([
            'parent_id' => null,
            'range_id' => $rangeId,
            'range_type' => $rangeType,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'title' => $this->title,
            'purpose' => $purpose ?: $this->purpose,
            'position' => 0,
            'payload' => $this->payload,
        ]);

        $element->store();

        $file_ref_id = $this->copyImage($user, $element);
        $element->image_id = $file_ref_id;
        $element->store();

        $this->copyContainers($user, $element);

        $this->copyChildren($user, $element, $purpose);

        return $element;
    }

    /**
     * Copies this instance as a child into another structural element.
     *
     * @param User              $user   this user will be the owner of the copy
     * @param StructuralElement $parent the target where to copy this instance
     * @param string $purpose the purpose of copying this instance
     * @param string $recursiveId the optional mapping id for copying child structural elements upon recursive call to this function
     *
     * @return StructuralElement the copy of this instance
     */
    public function copy(User $user, StructuralElement $parent, string $purpose = '', string $recursiveId = ''): StructuralElement
    {
        $ancestorIds = array_column($parent->findAncestors(), 'id');
        $ancestorIds[] = $parent->id;
        if (in_array($this->id, $ancestorIds)) {
            throw new \InvalidArgumentException('Cannot copy into descendants.');
        }
        static $mapping = [];

        $file_ref_id = $this->copyImage($user, $parent);

        $element = self::build([
            'parent_id' => $parent->id,
            'range_id' => $parent->range_id,
            'range_type' => $parent->range_type,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'title' => $this->title,
            'purpose' => empty($purpose) ? $this->purpose : $purpose,
            'position' => $parent->countChildren(),
            'payload' => $this->payload,
            'image_id' => $file_ref_id,
            'read_approval' => $parent->read_approval,
            'write_approval' => $parent->write_approval
        ]);

        $element->store();

        list($containerMap, $blockMap) = $this->copyContainers($user, $element);

        $mappingId = $recursiveId === '' ? $this->id . '_' . $element->id : $recursiveId;
        if (!isset($mapping[$mappingId])) {
            $mapping[$mappingId] = [
                'elements'   => [],
                'containers' => [],
                'blocks'     => [],
            ];
        }
        $mapping[$mappingId]['elements'][$this->id] = $element->id;
        $mapping[$mappingId]['containers'] = $mapping[$mappingId]['containers'] + $containerMap;
        $mapping[$mappingId]['blocks'] = $mapping[$mappingId]['blocks'] + $blockMap;

        $this->copyChildren($user, $element, $purpose, $mappingId);

        if ($recursiveId === '') {
            $this->performMapping($mapping[$mappingId]);
            unset($mapping[$mappingId]);
        }

        return $element;
    }

    private function copyImage(User $user, StructuralElement $parent) : ?string
    {
        $file_ref_id = null;

        /** @var ?\FileRef $original_file_ref */
        $original_file_ref = \FileRef::find($this->image_id);
        if ($original_file_ref) {
            $instance = new Instance($this->getCourseware($parent->range_id, $parent->range_type));
            $folder = \Courseware\Filesystem\PublicFolder::findOrCreateTopFolder($instance);
            /** @var \FileRef $file_ref */
            $file_ref = \FileManager::copyFile($original_file_ref->getFileType(), $folder, $user);
            $file_ref_id = $file_ref->id;
        }

        return $file_ref_id;
    }

    public function merge(User $user, StructuralElement $target): StructuralElement
    {
        // merge with target
        if (!$target->image_id) {
            $target->image_id = $this->copyImage($user, $target);
        }

        if ($target->title === 'neue Seite' || $target->title === 'New page') {
            $target->title = $this->title;
        }

        if (!$target->purpose) {
            $target->purpose = $this->purpose;
        }

        if (!$target->payload['color']) {
            $target->payload['color'] = $this->payload['color'];
        }

        if (!$target->payload['description']) {
            $target->payload['description'] = $this->payload['description'];
        }

        if (!$target->payload['license_type']) {
            $target->payload['license_type'] = $this->payload['license_type'];
        }

        if (!$target->payload['required_time']) {
            $target->payload['required_time'] = $this->payload['required_time'];
        }

        if (!$target->payload['difficulty_start']) {
            $target->payload['difficulty_start'] = $this->payload['difficulty_start'];
        }

        if (!$target->payload['difficulty_end']) {
            $target->payload['difficulty_end'] = $this->payload['difficulty_end'];
        }

        $target->store();

        // add Containers to target
        $this->copyContainers($user, $target);

        // copy Children
        $this->copyChildren($user, $target);

        return $this;
    }

    private function copyContainers(User $user, StructuralElement $newElement): array
    {
        $containerMap = [];
        $blockMap = [];
        foreach ($this->containers as $container) {
            list($newContainer, $blockMapsObjs) = $container->copy($user, $newElement);
            $containerMap[$container->id] = $newContainer->id;
            $blockMap = $blockMap + $blockMapsObjs;
        }
        return [$containerMap, $blockMap];
    }

    private function copyChildren(User $user, StructuralElement $newElement, string $purpose = '', string $recursiveId = ''): void
    {
        foreach ($this->children as $child) {
            $child->copy($user, $newElement, $purpose, $recursiveId);
        }
    }

    public function link(User $user, StructuralElement $parent): StructuralElement
    {
        $element = self::build([
            'parent_id' => $parent->id,
            'is_link' => 1,
            'target_id' => $this->id,
            'range_id' => $parent->range_id,
            'range_type' => $parent->range_type,
            'owner_id' => $user->id,
            'editor_id' => $user->id,
            'edit_blocker_id' => null,
            'title' => $this->title,
            'purpose' => $this->purpose,
            'position' => $parent->countChildren(),
            'payload' => $this->payload,
            'read_approval' => $parent->read_approval,
            'write_approval' => $parent->write_approval
        ]);

        $element->store();

        $this->linkChildren($user, $element);

        return $element;
    }

    private function linkChildren(User $user, StructuralElement $newElement): void
    {
        $children = self::findBySQL('parent_id = ?', [$this->id]);

        foreach ($children as $child) {
            $child->link($user, $newElement);
        }
    }

    public function pdfExport($user, bool $with_children = false)
    {
        $doc = new \ExportPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $doc->setHeaderTitle(_('Courseware'));
        if ($this->course) {
            $doc->setHeaderTitle(sprintf(_('Courseware aus %s'), $this->course->name));
        }
        if ($this->user) {
            $doc->setHeaderTitle(sprintf(_('Courseware von %s'), $this->user->getFullname()));
        }

        if (!self::canVisit($user)) {
            $doc->addPage();
            $doc->addContent(_('Diese Seite steht Ihnen nicht zur Verfügung!'));

            return $doc;
        }

        $this->getElementPdfExport(0, $with_children, $user, $doc);

        if ($with_children) {
            $doc->addTOCPage();
            $doc->SetFont('helvetica', 'B', 16);
            $doc->MultiCell(0, 0, _('Inhaltsverzeichnis'), 0, 'C', 0, 1, '', '', true, 0);
            $doc->Ln();
            $doc->SetFont('helvetica', '', 12);
            $doc->addTOC(1, 'helvetica', '.', _('Inhaltsverzeichnis'), 'B', array(0,0,0));
            $doc->endTOCPage();
        }


        return $doc;
    }

    private function getElementPdfExport(int $depth, bool $with_children, $user, $doc)
    {
        if (!$this->canVisit($user)) {
            return '';
        }
        $doc->addPage();
        $doc->Bookmark(htmlReady($this->title), $depth, 0, '', '', array(128,0,0));
        $html = "<h1>" . htmlReady($this->title) . "</h1>";
        $html .= $this->getContainerPdfExport();
        $doc->writeHTML($html);

        if ($with_children) {
            $this->getChildrenPdfExport($depth, $with_children, $user, $doc);
        }
    }

    private function getChildrenPdfExport(int $depth, bool $with_children, $user, $doc)
    {
        $children = self::findBySQL('parent_id = ?', [$this->id]);
        $depth++;
        foreach ($children as $child) {
            $child->getElementPdfExport($depth, $with_children, $user, $doc);
        }
    }

    private function getContainerPdfExport()
    {
        $containers = \Courseware\Container::findBySQL('structural_element_id = ?', [$this->id]);

        $html = '';
        foreach ($containers as $container) {
            $container_html_template = $container->type->getPdfHtmlTemplate();
            $html .= $container_html_template ? $container_html_template->render() : '';
        }

        return $html;
    }

    private function findParentTask()
    {
        if ($this->isRootNode()) {
            return null;
        }
        if ($this->task) {
            return $this->task;
        }

        return $this->parent->findParentTask();
    }

    private function performMapping($mapping)
    {
        // Blocks mapping.
        foreach ($mapping['blocks'] as $oldBlockId => $newBlockObj) {
            if ($newBlockObj->type->getType() === \Courseware\BlockTypes\Link::getType()) {
                $payload = $newBlockObj->type->getPayload();
                if ($payload['type'] === 'internal' && '' != $payload['target']) {
                    if (in_array($payload['target'], array_keys($mapping['elements']))) {
                        $payload['target'] = $mapping['elements'][intval($payload['target'])];
                    } else {
                        $payload['target'] = '';
                    }
                    $newBlockObj->type->setPayload($payload);
                    $newBlockObj->store();
                }
            }
        }
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(\StoredUserData $storage)
    {
        $structuralElements = \DBManager::get()->fetchAll(
            'SELECT * FROM cw_structural_elements WHERE ? IN (owner_id, editor_id, range_id)',
            [$storage->user_id]
        );
        if ($structuralElements) {
            $storage->addTabularData(_('Courseware Seiten'), 'cw_structural_elements', $structuralElements);
        }
        
    }
}
