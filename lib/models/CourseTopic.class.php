<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author     Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string $id alias column for issue_id
 * @property string $issue_id database column
 * @property string $seminar_id database column
 * @property string $author_id database column
 * @property I18NString $title database column
 * @property I18NString $description database column
 * @property int $priority database column
 * @property int $paper_related database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property SimpleORMapCollection|Folder[] $folders has_many Folder
 * @property Course $course belongs_to Course
 * @property User $author belongs_to User
 * @property SimpleORMapCollection|CourseDate[] $dates has_and_belongs_to_many CourseDate
 * @property-read mixed $forum_thread_url additional field
 */
class CourseTopic extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'themen';
        $config['has_and_belongs_to_many']['dates'] = [
            'class_name' => CourseDate::class,
            'thru_table' => 'themen_termine',
            'order_by'   => 'ORDER BY date',
            'on_delete'  => 'delete',
            'on_store'   => 'store'
        ];
        $config['has_many']['folders'] = [
            'class_name'  => Folder::class,
            'assoc_func' => 'findByTopic_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'seminar_id'
        ];
        $config['belongs_to']['author'] = [
            'class_name'  => User::class,
            'foreign_key' => 'author_id'
        ];

        $config['additional_fields']['forum_thread_url']['get'] = 'getForumThreadURL';

        $config['registered_callbacks']['before_create'][] = 'cbDefaultValues';
        $config['registered_callbacks']['after_store'][] = 'cbUpdateConnectedContentModules';
        $config['registered_callbacks']['before_delete'][] = 'cbUnlinkConnectedContentModules';

        $config['i18n_fields']['title'] = true;
        $config['i18n_fields']['description'] = true;

        parent::configure($config);
    }

    public static function findByTermin_id($termin_id)
    {
        return self::findBySQL("INNER JOIN themen_termine USING (issue_id)
            WHERE themen_termine.termin_id = ?
            ORDER BY priority ASC",
            [$termin_id]
        );
    }

    public static function findBySeminar_id($seminar_id, $order_by = 'ORDER BY priority')
    {
        return parent::findBySeminar_id($seminar_id, $order_by);
    }

    public static function findByTitle($seminar_id, $name)
    {
        return self::findOneBySQL("seminar_id = ? AND title = ?", [$seminar_id, $name]);
    }

    public static function getMaxPriority($seminar_id)
    {
        return DBManager::get()->fetchColumn("SELECT MAX(priority) FROM themen WHERE seminar_id=?", [$seminar_id]);
    }

    /**
    * set or update connection with document folder
    */
    public function connectWithDocumentFolder()
    {
        if ($this->seminar_id) {
            $document_module = Seminar::getInstance($this->seminar_id)->getSlotModule('documents');
            if ($document_module) {
                if (!$this->folders->count()) {
                    $folder = new Folder();
                    $folder['range_id'] = $this['seminar_id'];
                    $folder['parent_id'] = Folder::findTopFolder($this['seminar_id'])->getId();
                    $folder['range_type'] = "course";
                    $folder['folder_type'] = "CourseTopicFolder";
                    $folder['data_content']['topic_id'] = $this->getId();
                    $folder['user_id'] = $GLOBALS['user']->id;
                    $folder['name'] = $this['title'];
                    $folder['description'] = $this['description'];
                    return $folder->store();
                }
            }
        }
        return false;
    }

    /**
    * set or update connection with forum thread
    */
    public function connectWithForumThread()
    {
        if ($this->seminar_id) {
            $forum_module = Seminar::getInstance($this->seminar_id)->getSlotModule('forum');
            if ($forum_module instanceOf ForumModule) {
                $forum_module->setThreadForIssue($this->id, $this->title, $this->description);
                return true;
            }
        }
        return false;
    }

    public function getForumThreadURL()
    {
        if ($this->seminar_id) {
            $forum_module = Seminar::getInstance($this->seminar_id)->getSlotModule('forum');
            if ($forum_module instanceOf ForumModule) {
                return html_entity_decode($forum_module->getLinkToThread($this->id));
            }
        }
        return '';
    }

    protected function cbUpdateConnectedContentModules()
    {
        if ($this->isFieldDirty('title') || $this->isFieldDirty('description')) {
            if ($this->forum_thread_url) {
                $this->connectWithForumThread();
            }
        }
    }

    /**
     * Removes link information for forum topic and remove forum topic as well
     * if it is empty.
     */
    protected function cbUnlinkConnectedContentModules()
    {
        $query = "DELETE fei, fe
                  FROM `forum_entries_issues` AS fei
                  LEFT JOIN `forum_entries` AS fe
                    ON fei.`topic_id` = fe.`topic_id` AND fe.`rgt` = fe.`lft` + 1
                  WHERE `issue_id` = ?";
        DBManager::get()->execute($query, [$this->id]);
    }

    protected function cbDefaultValues()
    {
        if (empty($this->content['priority'])) {
            $this->content['priority'] = self::getMaxPriority($this->seminar_id) + 1;
        }
    }

    /**
     * return all filerefs belonging to this topic, permissions fpr given user are checked
     *
     * @param string|User $user_or_id
     * @return mixed[] A mixed array with FolderType and FileRef objects.
     */
    public function getAccessibleFolderFiles($user_or_id)
    {
        $user_id = $user_or_id instanceof User ? $user_or_id->id : $user_or_id;
        $all_files = [];
        $all_folders = [];
        $folders = $this->folders->getArrayCopy();
        foreach ($this->dates as $date) {
            $folders = array_merge($folders, $date->folders->getArrayCopy());
        }
        foreach ($folders as $folder) {
            [$files, $typed_folders] = array_values(FileManager::getFolderFilesRecursive($folder->getTypedFolder(), $user_id));
            foreach ($files as $file) {
                $all_files[$file->id] = $file;
            }
            $all_folders = array_merge($all_folders, $typed_folders);
        }
        return ['files' => $all_files, 'folders' => $all_folders];
    }

    /**
     * Increases the priority of this topic. Meaning the topic will be sorted further up.
     * Be aware that this actually decreases the priority property since lower numbers
     * mean higher priority.
     *
     * @return boolean
     * @todo Deprecated, remove for Stud.IP 6.0
     */
    public function increasePriority()
    {
        // Update all the course's topics with a lower priority than this one
        $query = "UPDATE `themen`
                  SET `priority` = `priority` + 1
                  WHERE `seminar_id` = :course_id
                    AND `priority` < :current_priority
                  ORDER BY `priority` DESC
                  LIMIT 1";
        $changed = DBManager::get()->execute($query, [
            ':course_id'        => $this->seminar_id,
            ':current_priority' => $this->priority,
        ]);

        // If anything has changed, decrease priority. Otherwise the current
        // topic is already at top.
        if ($changed) {
            $this->priority -= 1;
            $this->store();
            return true;
        }

        return false;
    }

    /**
     * Decreases the priority of this topic. Meaning the topic will be sorted further down.
     * Be aware that this actually increases the priority property since higher numbers
     * mean lower priority.
     *
     * @todo Deprecated, remove for Stud.IP 6.0
     */
    public function decreasePriority()
    {
        // Update all the course's topics with a higher priority than this one
        $query = "UPDATE `themen`
                 SET `priority` = `priority` - 1
                  WHERE `seminar_id` = :course_id
                    AND `priority` > :current_priority
                 ORDER BY `priority` ASC
                 LIMIT 1";
        $changed = DBManager::get()->execute($query, [
            ':course_id'        => $this->seminar_id,
            ':current_priority' => $this->priority,
        ]);

        // If anything has changed, increase priority. Otherwise the current
        // topic is already at bottom.
        if ($changed) {
            $this->priority += 1;
            $this->store();
            return true;
        }

        return false;

    }
}
