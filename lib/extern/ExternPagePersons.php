<?php
/**
 * ExternPagePersons.php - Class to provide a list of members of institutes.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.4
 */

class ExternPagePersons extends ExternPage
{

    public $institute;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->institute = Institute::find($this->page_config->range_id);
    }

    public function getSortFields(): array
    {
        return [
            'vorname'     => _('Vorname'),
            'nachname'    => _('Nachname'),
            'email'       => _('E-Mail'),
            'title_front' => _('Titel vorangestellt'),
            'title_rear'  => _('Titel nachgestellt')
        ];
    }

    public function getDataFields($object_classes = []): array
    {
        return parent::getDataFields(
            [
                'user',
                'userinstrole'
            ]
        );
    }

    public function getConfigFields($as_array = false)
    {
        $args = '
            sort          option,
            grouping      bool,
            language      option,
            groupsvisible intArray,
            groupsalias   getArray,
            escaping      option
        ';
        return $as_array ? self::argsToArray($args) : $args;
    }

    public function getAllowedRequestParams($as_array = false)
    {
        $params = [
            'grouping',
            'language',
            'sort',
        ];
        return $as_array ? $params : implode(',', $params);
    }

    public function getMarkersContents(): array
    {
        if ($this->grouping) {
            return  [
                'GROUPS' => $this->getGroupedContent()
            ];
        } else {
            return [
                'PERSONS' => $this->getContentUsers()
            ];
        }
    }

    protected function getContentUsers()
    {
        $content = [];
        foreach ($this->institute->members->orderBy($this->sort) as $member) {
            $content[] = $this->getUserContent($member);
        }
        return $content;
    }

    /**
     * Returns all marker content for grouped view.
     *
     * @return array The array with all markers.
     */
    protected function getGroupedContent()
    {
        $content = [];
        foreach ($this->institute->status_groups->orderBy('position') as $top_group) {
            foreach ($this->getAllChildren($top_group) as $group) {
                if ($this->groupsvisible[$group->id]) {
                    if ($this->groupsalias[$group->id]) {
                        $grouptitle_substitute = $this->groupsalias[$group->id];
                    } else {
                        $grouptitle_substitute = '';
                    }
                    $content[] = [
                        'GROUPTITLE'            => $group->name,
                        'GROUPTITLE_SUBSTITUTE' => $grouptitle_substitute,
                        'PERSONS'               => $this->getContentGroupedUsers($group->members),
                    ];
                }
            }
        }
        return $content;
    }

    private function getContentGroupedUsers($members)
    {
        $content = [];
        foreach ($members as $member) {
            $content[] = $this->getUserContent($member);
        }
        return $content;
    }

    /**
     * Returns all child groups of a group.
     *
     * @param Statusgruppen $group The parent group.
     * @return Statusgruppen[] All child groups.
     */
    protected function getAllChildren(Statusgruppen $group)
    {
        $all_groups[] = $group;
        foreach ($group->getChildren() as $child) {
            $all_groups = array_merge($all_groups, $this->getAllChildren($child));
        }
        return $all_groups;
    }

    /**
     * Get content of a single user.
     *
     * @param StatusgruppeUser $member
     * @return array
     */
    protected function getUserContent($member)
    {
        if (Visibility::verify('picture', $member->user_id) == 5) {
            $avatar = Avatar::getAvatar($member->user_id);
        } else {
            $avatar = Avatar::getNobody();
        }
        $inst_member = $this->institute->members->findOneBy('user_id', $member->user_id);
        $content = array_merge(
            [
                'FULLNAME'         => $member->user->getFullName(),
                'LASTNAME'         => $member->user->Nachname,
                'FIRSTNAME'        => $member->user->Vorname,
                'TITLEFRONT'       => $member->user->title_front,
                'TITLEREAR'        => $member->user->title_rear,
                'USERNAME'         => $member->user->username,
                'ID'               => $member->user_id,
                'IMAGE_URL_SMALL'  => $avatar->getURL(Avatar::SMALL),
                'IMAGE_URL_MEDIUM' => $avatar->getURL(Avatar::MEDIUM),
                'IMAGE_URL_NORMAL' => $avatar->getURL(Avatar::NORMAL),
                'PHONE'            => $inst_member->telefon,
                'ROOM'             => $inst_member->raum,
                'EMAIL'            => get_visible_email($member->user->user_id),
                'HOMEPAGE_URL'     => Visibility::verify('homepage', $member->user_id) ? $member->user->home : '',
                'OFFICEHOURS'      => $inst_member->sprechzeiten
            ],
            $this->getDatafieldMarkers($member->user),
            $this->getDatafieldMarkers($member));
        return $content;
    }

    public function getFullGroupNames()
    {
        $groups = [];
        foreach ($this->institute->status_groups as $status_group) {
            $groups[$status_group->id] = $status_group->name;
            $groups = array_merge($groups, $this->getGroupPaths($status_group));
        }
        return $groups;
    }

    public function getGroupPaths($group, $seperator = ' > ', $pre = '')
    {
        $name = $pre
            ? $pre . $seperator . $group->getName()
            : $group->getName();
        $result[$group->id] = $name;
        if ($group->children) {
            foreach ($group->children as $child) {
                $result = array_merge($result, $this->getGroupPaths($child, $seperator, $name));
            }
        }
        return $result;
    }

}
