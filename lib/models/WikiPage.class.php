<?php
/**
 * WikiPage.class.php
 * model class for table wiki
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author    mlunzena
 * @copyright (c) Authors
 *
 * @property array $id alias for pk
 * @property string $course_id database column
 * @property string|null $user_id database column
 * @property string $name database column
 * @property string $content database column
 * @property string|null $ancestor database column
 * @property int|null $chdate database column
 * @property int $version database column
 * @property int|null $mkdate database column
 * @property User|null $user belongs_to User
 * @property Course $course belongs_to Course
 * @property-read mixed $parent additional field
 * @property-read mixed $children additional field
 * @property-read mixed $config additional field
 */
class WikiPage extends SimpleORMap implements PrivacyObject
{
    /**
     * Configures the model
     * @param  array  $config Configuration
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'wiki_pages';

        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];
        $config['belongs_to']['course'] = [
            'class_name'  => Course::class,
            'foreign_key' => 'range_id',
        ];
        $config['has_many']['versions'] = [
            'class_name'  => WikiVersion::class,
            'foreign_key' => 'page_id',
            'order_by'    => 'ORDER BY mkdate DESC',
            'on_delete'   => 'delete',
        ];
        $config['has_many']['onlineeditingusers'] = [
            'class_name' => WikiOnlineEditingUser::class,
            'foreign_key' => 'page_id',
            'on_delete'   => 'delete',
        ];

        $config['additional_fields']['parent'] = [
            'get' => function ($page) {
                return \WikiPage::find($page->parent_id);
            }
        ];

        $config['additional_fields']['children'] = [
            'get' => function ($page) {
                return self::findBySQL('parent_id = ?', [
                    $page->id
                ]);
            }
        ];
        $config['additional_fields']['predecessor'] = [
            'get' => function ($page) {
                return $page->versions ? $page->versions[0] : null;
            }
        ];
        $config['additional_fields']['versionnumber'] = [
            'get' => function ($page) {
                return count($page->versions) + 1;
            }
        ];

        $config['registered_callbacks']['before_store'][] = 'createVersion';
        $config['default_values']['last_author'] = 'nobody';

        parent::configure($config);
    }


    protected function createVersion()
    {
        $this->user_id = User::findCurrent()->id;
        if (
            !$this->isNew()
            &&  $this->content['content'] !== $this->content_db['content']
            && (
                $this->content_db['user_id'] !== $this->content['user_id']
                || $this->content_db['chdate'] < time() - 60 * 30
            )
        ) {
            //Neue Version anlegen:
            WikiVersion::create([
                'page_id' => $this->id,
                'name'    => $this->content_db['name'],
                'content' => $this->content_db['content'],
                'user_id' => $this->content_db['user_id'],
                'mkdate'  => $this->content_db['chdate'],
            ]);
        }
        return true;
    }

    public static function findByName($range_id, $name)
    {
        return self::findOneBySQL('name = :name AND range_id = :range_id', [
            'range_id' => $range_id,
            'name' => $name
        ]);
    }


    /**
     * Returns whether this page is visible to the given user.
     * @param  mixed  $user User object or id
     * @return boolean indicating whether the page is visible
     */
    public function isReadable(?string $user_id = null): bool
    {
        if ($this->isNew()) {
            return true;
        }
        // anyone can see this page if it belongs to a free course
        if (
            $this->read_permission === 'all'
            && Config::get()->ENABLE_FREE_ACCESS
            && $this->course
            && !$this->course->lesezugriff
        ) {
            return true;
        }
        if ($user_id === null) {
            $user_id = User::findCurrent()->id;
        }

        if (
            $this->read_permission === 'all'
            && $GLOBALS['perm']->have_studip_perm('user', $this->range_id, $user_id)
        ) {
            return true;
        }

        if ($GLOBALS['perm']->have_studip_perm(
            'dozent',
            $this->range_id,
            $user_id
        )) {
            return true;
        }

        if (in_array($this->read_permission, ['tutor', 'dozent'])) {
            return $GLOBALS['perm']->have_studip_perm($this->read_permission, $this->range_id, $user_id);
        } else {
            return StatusgruppeUser::exists([$this->read_permission, $user_id]);
        }
    }

    /**
     * Returns whether this page is editable to the given user.
     * @param  string  $user_id the ID of the user
     * @return boolean indicating whether the page is editable
     */
    public function isEditable(?string $user_id = null): bool
    {
        if ($user_id === null) {
            $user_id = User::findCurrent()->id;
        }
        if ($GLOBALS['perm']->have_studip_perm(
            'dozent',
            $this->range_id,
            $user_id
        )) {
            return true;
        }
        if ($this->write_permission === 'all') {
            return true;
        }
        if (in_array($this->write_permission, ['tutor', 'dozent'])) {
            return $GLOBALS['perm']->have_studip_perm(
                $this->write_permission,
                $this->range_id,
                $user_id
            );
        } else {
            return StatusgruppeUser::exists([$this->write_permission, $user_id]);
        }
    }


    /**
     * Returns the start page of a wiki for a given course. The start page has
     * the keyword 'WikiWikiWeb'.
     *
     * @param  string $range_id Course id
     * @return WikiPage
     */
    public static function getStartPage($range_id)
    {
        $page_id = CourseConfig::get($range_id)->WIKI_STARTPAGE_ID;

        if ($page_id) {
            return self::find($page_id);
        }

        $page = new WikiPage();
        $pagename = _('Startseite');
        $page->content = _('Dieses Wiki ist noch leer.');
        if ($page->isEditable()) {
            $page->content .=  ' ' . _("Bearbeiten Sie es!\nNeue Seiten oder Links werden einfach durch Eingeben von [nop][[Wikinamen]][/nop] in doppelten eckigen Klammern angelegt.");
        }
        return $page;
    }

    /**
     * Export available data of a given user into a storage object
     * (an instance of the StoredUserData class) for that user.
     *
     * @param StoredUserData $storage object to store data into
     */
    public static function exportUserData(StoredUserData $storage)
    {
        $sorm = self::findBySQL("user_id = ?", [$storage->user_id]);
        if ($sorm) {
            $field_data = [];
            foreach ($sorm as $row) {
                $field_data[] = $row->toRawArray();
            }
            if ($field_data) {
                $storage->addTabularData(_('Wiki Einträge'), 'wiki', $field_data);
            }
        }
    }


    /**
     * Tests if a given Wikipage name (keyword) is a valid ancestor for this page.
     *
     * @param   string   ancestor Wikipage name to be tested to be an ancestor
     * @return  boolean  true if ok, false if not
     *
     */
    public function isValidAncestor($ancestor)
    {
        if ($this->name === 'WikiWikiWeb' || $this->name === $ancestor) {
            return false;
        }

        $keywords = array_map(
            function ($descendant) {
                return $descendant->name;
            },
            $this->getDescendants()
        );

        return !in_array($ancestor, $keywords);
    }

    /**
     * Retrieve an array of all descending WikiPages (recursive).
     *
     * @return   array   Array of all descendant WikiPages
     *
     */
    public function getDescendants()
    {
        $descendants = [];

        foreach ($this->children as $child) {
            array_push($descendants, $child, ...$child->getDescendants());
        }

        return $descendants;
    }

    public function getOnlineUsers(): array
    {
        $users = [];
        WikiOnlineEditingUser::deleteBySQL(
            "`page_id` = :page_id AND `chdate` < UNIX_TIMESTAMP() - :threshold",
            [
                'page_id' => $this->id,
                'threshold' => WikiOnlineEditingUser::$threshold
            ]
        );
        return $this->onlineeditingusers->map(function (WikiOnlineEditingUser $editing_user) {
            return [
                'user_id' => $editing_user->user_id,
                'username' => $editing_user->user->username,
                'fullname' => $editing_user->user->getFullName(),
                'avatar' => Avatar::getAvatar($editing_user->user_id)->getURL(Avatar::SMALL),
                'editing' => (bool) $editing_user->editing,
                'editing_request' => (bool) $editing_user->editing_request,
            ];
        });
    }
}
