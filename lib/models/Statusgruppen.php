<?php

/**
 * Statusgruppen.php
 * model class for statusgroups.
 * The statusgrouphierarchy is represented by the attributes
 * children and parent
 *
 * Statusgroupmembers are saved as in <code>$this->members</code>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias column for statusgruppe_id
 * @property string $statusgruppe_id database column
 * @property I18NString $name database column
 * @property string|null $description database column
 * @property string $range_id database column
 * @property int $position database column
 * @property int $size database column
 * @property int $selfassign database column
 * @property int $selfassign_start database column
 * @property int $selfassign_end database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $calendar_group database column
 * @property I18NString|null $name_w database column
 * @property I18NString|null $name_m database column
 * @property SimpleORMapCollection|StatusgruppeUser[] $members has_many StatusgruppeUser
 * @property SimpleORMapCollection|ConsultationBlock[] $consultation_blocks has_many ConsultationBlock
 * @property SimpleORMapCollection|ConsultationResponsibility[] $consultation_responsibilities has_many ConsultationResponsibility
 * @property Statusgruppen $parent belongs_to Statusgruppen
 * @property Course $course belongs_to Course
 * @property Institute $institute belongs_to Institute
 * @property User $user belongs_to User
 * @property BlubberStatusgruppeThread $blubberthread has_one BlubberStatusgruppeThread
 * @property SimpleORMapCollection|CourseDate[] $dates has_and_belongs_to_many CourseDate
 * @property mixed $children additional field
 * @property-read Range|null $range
 */
class Statusgruppen extends SimpleORMap implements PrivacyObject
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'statusgruppen';
        $config['has_many']['members'] = [
            'class_name'        => StatusgruppeUser::class,
            'assoc_foreign_key' => 'statusgruppe_id',
            'on_delete'         => 'delete',
            'order_by'          => 'ORDER BY position ASC',
        ];
        $config['has_and_belongs_to_many']['dates'] = [
            'class_name' => CourseDate::class,
            'thru_table' => 'termin_related_groups',
            'order_by'   => 'ORDER BY date',
            'on_delete'  => 'delete', // TODO: This might cause trouble
            'on_store'   => 'store'
        ];
        $config['belongs_to']['parent'] = [
            'class_name'  => Statusgruppen::class,
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['institute'] = [
            'class_name'  => Institute::class,
            'foreign_key' => 'range_id',
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'range_id',
        ];
        $config['has_one']['blubberthread'] = [
            'class_name' => BlubberStatusgruppeThread::class,
            'assoc_func' => 'findByStatusgruppe_id',
            'on_store' => 'store',
            'on_delete' => 'delete'
        ];
        $config['has_many']['consultation_blocks'] = [
            'class_name'        => ConsultationBlock::class,
            'assoc_foreign_key' => 'range_id',
            'on_delete'         => 'delete',
        ];
        $config['has_many']['consultation_responsibilities'] = [
            'class_name'        => ConsultationResponsibility::class,
            'assoc_func'        => 'findByStatusgroupId',
            'on_delete'         => 'delete',
        ];

        $config['additional_fields']['children'] = true;

        $config['additional_fields']['range'] = [
            'get' => function (Statusgruppen $group): ?Range {
                return RangeFactory::find($group->range_id);
            },
        ];

        $config['default_values']['position'] = null;

        $config['registered_callbacks']['before_store'][] = 'cbAddPosition';
        $config['registered_callbacks']['after_delete'][] = 'cbReorderPositions';
        $config['registered_callbacks']['after_delete'][] = 'cbRemoveTasks';
        $config['i18n_fields']['name'] = true;
        $config['i18n_fields']['name_w'] = true;
        $config['i18n_fields']['name_m'] = true;

        parent::configure($config);
    }

    public $keep_children = false;

    public static function findAllByRangeId($range_id, $as_collection = false)
    {
        $groups = self::findBySQL('range_id IN (?)', [$range_id]);
        if (count($groups) > 0) {
            $ids = array_map(function ($group) { return $group->id; }, $groups);
            $groups = array_merge($groups, self::findAllByRangeId($ids, false));
        }

        return $as_collection
             ? SimpleCollection::createFromArray($groups)
             : $groups;
    }

    /**
     * Creates or updates a statusgroup.
     *
     * @param string      $id                ID of an existing group or empty if new group
     * @param string      $name              group name
     * @param int         $position          position or null if automatic position after other groups
     * @param string      $range_id          ID of the object this group belongs to
     * @param int         $size              max number of members or 0 if unlimited
     * @param int         $selfassign        may users join this group by themselves?
     * @param int         $selfassign_start  group joining is possible starting at ...
     * @param int         $makefolder        create a document folder assigned to this group?
     * @param array|null  $dates             dates assigned to this group. Defaults to null which means already assigned
     *                                       dates are not changed.
     * @return Statusgruppen The saved statusgroup.
     * @throws Exception
     */
    public static function createOrUpdate(
        $id,
        $name,
        $position,
        $range_id,
        $size,
        $selfassign,
        $selfassign_start,
        $selfassign_end,
        $makefolder,
        $dates = null
    )
    {
        $group = new Statusgruppen($id);

        $group->name = $name;
        $group->position = $position;
        $group->range_id = $range_id;
        $group->size = $size;
        $group->selfassign = $selfassign;
        $group->selfassign_start = $selfassign ? $selfassign_start : 0;
        $group->selfassign_end = $selfassign ? $selfassign_end : 0;

        // Set assigned dates.
        if (isset($dates)) {
            $group->dates = CourseDate::findMany($dates);
        }

        $group->store();

        /*
         * Create document folder if requested (ID is needed here,
         * so we do that after store()).
         */
        $group->updateFolder($makefolder);

        return $group;
    }

    public function getChildren()
    {
        $result = Statusgruppen::findBySQL('range_id = ? ORDER BY position', [$this->id]);
        return $result ?: [];
    }

    public function getDatafields()
    {
        return DataFieldEntry::getDataFieldEntries([$this->range_id, $this->statusgruppe_id], 'roleinstdata');
    }

    public function setDatafields($data)
    {
        foreach ($this->getDatafields() as $field) {
            $field->setValueFromSubmit($data[$field->getId()]);
            $field->store();
        }
    }

    /**
     * Finds all statusgroups by a course id
     *
     * @param string The course id
     * @return array Statusgroups
     */
    public static function findBySeminar_id($course_id)
    {
        return self::findByRange_id($course_id, 'ORDER BY position asc, name asc');
    }

    public static function findByTermin_id($termin_id)
    {
        return self::findBySQL('INNER JOIN termin_related_groups USING (statusgruppe_id) WHERE termin_id = ?', [$termin_id]);
    }

    public static function findContactGroups($user_id = null)
    {
        return self::findByRange_id($user_id ?: $GLOBALS['user']->id);
    }

    /**
     * Find all groups belonging to the given range_id that may be joined
     * by the given user.
     *
     * @param String $range_id range_id the groups shall belong to
     * @param String $user_id user to check
     * @return array
     */
    public static function findJoinableGroups($range_id, $user_id)
    {
        $groups = self::findByRange_id($range_id);
        return array_filter($groups, function ($g) use ($user_id) { return $g->userMayJoin($user_id); });
    }

    /**
     * Reorders the positions in numeric order without gaps (e.g. after a delete).
     *
     * @param  string $range_id Id of range
     */
    public static function reorderPositionsForRange($range_id)
    {
        return self::findEachBySQL(
            function ($group, $index) {
                $group->position = $index;
                $group->store();
            },
            'range_id = ? ORDER BY position ASC, name ASC',
            [$range_id]
        );
    }

    /**
     * Produces an array of all statusgroups a user is in
     *
     * @param string $user_id The user_id
     * @param string $seperator The sign between the full paths
     * @param string $pre Preface of the outputted string (used for recursion)
     * @return array Stringarray of full gendered paths
     */
    public function getFullGenderedPaths($user_id, $seperator = " > ", $pre = "")
    {
        $result = [];
        $name = $pre
              ? $pre . $seperator . $this->getGenderedName($user_id)
              : $this->getGenderedName($user_id);
        if ($this->isMember($user_id)) {
            $result[] = $name;
        }
        if ($this->children) {
            foreach ($this->children as $child) {
                $result = array_merge($result, $child->getFullGenderedPaths($user_id, $seperator, $name));
            }
        }
        return $result;
    }

    /**
     * Produces string of all statusgroups a user is in (upwards from the
     * current group)
     *
     * @param string $user_id The user_id
     * @param string $seperator The sign between the full paths
     * @return array String of full gendered paths separated by given separator
     */
    public function getFullGenderedName($user_id, $seperator = ' > ')
    {
        $result = [$this->getGenderedName($user_id)];

        $item = $this;
        while ($item = $item->parent) {
            array_unshift($result, $item->getGenderedName($user_id));
        }

        return implode($seperator, $result);
    }

    /**
     * Returns the gendered name of a statusgroup
     *
     * @param string|User $user_id The user_id
     * @return string The gendered name
     */
    public function getGenderedName($user_or_id)
    {
        // We have to have at least 1 name gendered
        if ((string) $this->name_m || (string) $this->name_w) {
            $user = User::toObject($user_or_id);
            switch ($user->geschlecht) {
                case UserInfo::GENDER_FEMALE:
                    return (string) $this->name_w ?: $this->name;
                case UserInfo::GENDER_MALE:
                    return (string) $this->name_m ?: $this->name;
            }
        }
        return $this->name;
    }

    public function getName()
    {
        return $this->content['name'];
    }

    /**
     * Puts out an array of all gendered userroles for a user in a certain
     * context
     *
     * @param string $context The context
     * @param string $user The user id
     * @return array All roles
     */
    public static function getUserRoles($context, $user)
    {
        $roles = [];
        $groups = self::findByRange_id($context);
        foreach ($groups as $group) {
            $roles = array_merge($roles, $group->getFullGenderedPaths($user));
        }
        return $roles;
    }

    /**
     * Checks if a statusgroup has a folder.
     *
     * @return boolean <b>true</> if the statusgroup has a folder, else
     * <b>false</b>
     */
    public function hasFolder()
    {
        $query = "SELECT id FROM folders WHERE folder_type = 'CourseGroupFolder' AND range_id = ? AND data_content LIKE ? LIMIT 1";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->range_id, '%"group":"' . $this->id. '"%']);
        return $statement->fetchColumn();
    }

    /**
     * Gets the folder assigned to this statusgroup.
     *
     * @return CourseGroupFolder|null
     */
    public function getFolder()
    {
        $folder_id = $this->hasFolder();
        return $folder_id ? FileManager::getTypedFolder($folder_id) : null;
    }

    /**
     * Delete or create a folder
     * @param boolean $set <b>true</b> Create a folder
     * <b>false</b> Unlink the existing folder from the group
     */
    public function updateFolder($set)
    {
        // Keep existing folder, but disconnect it from group.
        if ($this->hasFolder() && !$set) {
            $folder = $this->getFolder();
            $folder->folder_type = 'StandardFolder';
            unset($folder->data_content['group']);
            return $folder->store();
        }

        // Update existing folder name
        if ($this->hasFolder() && $set) {
            $folder = $this->getFolder();
            $folder->name = _('Dateiordner der Gruppe:') . ' ' . $this->name;
            return $folder->store();
        }

        // Create new CourseGroupFolder under top folder.
        if (!$this->hasFolder() && $set) {
            $topFolder = Folder::findTopFolder($this->range_id);
            if ($topFolder) {
                $folderdata = [
                    'user_id' => $GLOBALS['user']->id,
                    'parent_id' => $topFolder->id,
                    'range_id' => $this->range_id,
                    'range_type' => 'course',
                    'folder_type' => 'CourseGroupFolder',
                    'name' => _('Dateiordner der Gruppe:') . ' ' . $this->name,
                    'data_content' => ['group' => $this->id],
                    'description' => _('Ablage für Ordner und Dokumente dieser Gruppe')
                ];
                $groupFolder = new CourseGroupFolder($folderdata);
                return $groupFolder->store();
            }
        }
    }

    /**
     * Finds CourseTopics assigned to this group via course dates.
     * @return array
     */
    public function findTopics()
    {
        $topics = [];
        foreach ($this->dates as $d) {
            foreach ($d->topics as $t) {
                // Assign topics with ID as key so we get unique entries.
                $topics[$t->id] = $t;
            }
        }
        return $topics;
    }

    /**
     * Finds Lecturers assigned to this group via course dates.
     * @return array
     */
    public function findLecturers()
    {
        $lecturers = [];
        foreach ($this->dates as $dates) {
            foreach ($dates->dozenten as $d) {
                // Assign topics with ID as key so we get unique entries.
                $lecturers[$d->id] = $d;
            }
        }
        return $lecturers;
    }

    /**
     * Checks if a user is a member of this group
     *
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is a member of this group
     */
    public function isMember($user_id = null)
    {
        if ($user_id == null) {
            $user_id = $GLOBALS['user']->id;
        }
        foreach ($this->members as $member) {
            if ($member->user_id == $user_id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Displayfunction to show the places left in this group
     *
     * @return string displaystring
     */
    public function getPlaces()
    {
        return $this->size ? "( " . min(count($this->members), $this->size) . " / {$this->size} )" : "";
    }

    /**
     * Remove all users of this group
     */
    public function removeAllUsers()
    {
        StatusgruppeUser::deleteBySQL('statusgruppe_id = ?', [$this->id]);
    }

    /**
     * Remove one user from this group
     *
     * @param string $user_id The user id
     * @param bool   $deep    Remove user from children as well?
     * @return bool
     */
    public function removeUser($user_id, $deep = false)
    {
        // Delete user from statusgruppe
        $member = StatusgruppeUser::find([$this->id, $user_id]);
        $result = $member !== null && $member->delete();

        if ($deep) {
            foreach ($this->children as $child) {
                $child->removeUser($user_id, true);
            }
        }

        return $result;
    }

    /**
     * Adds a user to a group
     *
     * @param string $user_id The user id
     * @param boolean $check if <b>true</b> checks if there is space left in
     * this group
     * @return boolean <b>true</b> if user was added
     */
    public function addUser($user_id, $check = false)
    {
        if ($check && !$this->userMayJoin($user_id)) {
            return false;
        }
        $user = new StatusgruppeUser([$this->id, $user_id]);

        // set up default datafield values for institute groups
        if ($user->isNew() && !Course::find($this->range_id)) {
            $user->datafields->each(function ($datafield) {
                // note: $datafield->content does not work here
                $datafield['content'] = 'default_value';
                $datafield->store();
            });
        }
        return $user->store();
    }

    /**
     * Checks if a user could join this group
     *
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is allowed to join
     */
    public function userMayJoin($user_id)
    {
        return !$this->isMember($user_id)
            && $this->hasSpace()
            && ($this->selfassign != 2 || !$this->userHasExclusiveGroup($user_id));
    }

    /**
     * Checks if a user could leave this group
     *
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is allowed to leave
     */
    public function userMayLeave($user_id)
    {
        return $this->isMember($user_id)
            && ($this->selfassign && (!$this->selfassign_end || $this->selfassign_end > time()));
    }

    /**
     * Checks if the user is already in an exclusive group of this range
     *
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user has already an exclusive group
     */
    public function userHasExclusiveGroup($user_id)
    {
        $sql = "SELECT 1 FROM statusgruppe_user JOIN statusgruppen USING (statusgruppe_id) WHERE selfassign = 2 AND range_id = ? AND user_id = ?";
        $stmt = DBManager::get()->prepare($sql);
        $stmt->execute([$this->range_id, $user_id]);
        return $stmt->fetchColumn();
    }

    /**
     * Sorts the member of a group alphabetic
     */
    public function sortMembersAlphabetic()
    {
        $i = 0;

        foreach ($this->members->orderBy('nachname, vorname') as $member) {
            $member->position = $i++;
            $member->store();
        }
    }

    /**
     * Sorts subgroups alphabetical
     */
    public function sortSubGroupsAlphabetic()
    {
        $groups = self::findBySQL('range_id = ? ORDER BY name', [$this->id]);

        foreach ($groups as $position => $group) {
            $group->position = $position;
            $group->store();
        }
    }

    /**
     * Checks if there is free space in this group
     *
     * @return <b>true</b> if there is free space
     */
    public function hasSpace()
    {
        return $this->selfassign &&
            ($this->selfassign_start <= time()) &&
            ($this->selfassign_end == 0 || $this->selfassign_end >= time()) &&
            ($this->size == 0 || count($this->members) < $this->size);
    }

    /**
     * Move a user to a position of a group
     *
     * @param string $user
     * @param type $pos
     */
    public function moveUser($user_id, $pos)
    {
        $statususer = new StatusgruppeUser([$this->id, $user_id]);
        if ($pos > $statususer->position) {
            $sql = "UPDATE statusgruppe_user SET position = position - 1 WHERE statusgruppe_id = ? AND position > ? AND position <= ?";
        } else {
            $sql = "UPDATE statusgruppe_user SET position = position + 1 WHERE statusgruppe_id = ? AND position < ? AND position >= ?";
        }
        $db = DBManager::get();
        $stmt = $db->prepare($sql);
        $stmt->execute([$this->id, $statususer->position, $pos]);

        $sql2 = "UPDATE statusgruppe_user SET position = ? WHERE statusgruppe_id = ? AND user_id = ?";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute([$pos, $this->id, $statususer->user_id]);
    }

    /**
     * Deletes a status group. Any associated child group will move upwards
     * in the tree.
     */
    public function remove()
    {
        // get all child-statusgroups and put them as a child of the father, so they don't hang around without a parent
        $children = $this->children->pluck('statusgruppe_id');
        if (!empty($children)) {
            $query = "UPDATE statusgruppen
                      SET range_id = ?
                      WHERE statusgruppe_id IN (?)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$this->range_id, $children]);
        }

        $old = $this->keep_children;
        $this->keep_children = true;

        $result = $this->delete();

        $this->keep_children = $old;

        return $result;
    }

    /**
     * Deletes a status group and all it's child groups.
     *
     * @return int number of deleted groups
     */
    public function delete()
    {
        $result = 0;
        if (!$this->keep_children) {
            foreach($this->children as $child) {
                $result += $child->delete();
            }

        }

        // Remove datafields
        DatafieldEntryModel::deleteBySQL('range_id = ?', [$this->id]);
        $result += parent::delete();

        return $result;
    }

    /**
     * Adds the next free position if position is null.
     */
    public function cbAddPosition()
    {
        if ($this->position === null) {
            $sql = "SELECT MAX(position) FROM statusgruppen WHERE range_id = ?";
            $stmt = DBManager::get()->prepare($sql);
            $stmt->execute([$this->range_id]);
            $max_position = $stmt->fetchColumn();
            $this->position = $max_position === null ? 0 : $max_position + 1;
        }
    }

    /**
     * Reorders position after delete or for the assoicated range_id.
     */
    public function cbReorderPositions()
    {
        if (self::$performs_batch_operation) {
            return;
        }

        self::reorderPositionsForRange($this->range_id);
    }

    /**
     * This callback is called after deleting a User.
     * It removes courseware task entries that are associated with the group.
     */
    public function cbRemoveTask()
    {
        \Courseware\Task::deleteBySQL(
            '`solver_id` = ? AND `solver_type`= "group"',
            [$this->id]
        );
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findThru($storage->user_id, [
            'thru_table'        => 'statusgruppe_user',
            'thru_key'          => 'user_id',
            'thru_assoc_key'    => 'statusgruppe_id',
            'assoc_foreign_key' => 'statusgruppe_id',
        ]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Statusgruppen'), 'statusgruppen', $field_data);
            }
        }
    }

    /**
     * Checks if a user is a member of a group.
     *
     * @param string $user_id The user id
     * @return boolean <b>true</b> if user is a member of this group
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public static function isMemberOf(string $gruppenId, string $userId): bool
    {
        return StatusgruppeUser::countBySql('statusgruppe_id = ? AND user_id = ?', [$gruppenId, $userId]) !== 0;
    }
}
