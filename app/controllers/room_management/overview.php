<?php

/**
 * overview.php - contains RoomManagement_OverviewController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @copyright   2017-2020
 * @category    Stud.IP
 * @since       4.5
 */


/**
 * RoomManagement_OverviewController contains general overview actions
 * for the new room management functionality.
 *
 * NOTE: It is derived from StudipController instead of AuthenticatedController
 * since the public_booking_plans action needs to be visible for
 * nobody users.
 */
class RoomManagement_OverviewController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        if ($action === 'public_booking_plans') {
            if (Config::get()->RESOURCES_SHOW_PUBLIC_ROOM_PLANS) {
                $this->allow_nobody = true;
            } else {
                throw new AccessDeniedException();
            }
        }
        parent::before_filter($action, $args);

        $this->user = User::findCurrent();
        $this->user_is_root = $GLOBALS['perm']->have_perm('root');
        $this->user_is_global_resource_user = ResourceManager::userHasGlobalPermission($this->user);
        $this->user_is_global_resource_admin = ResourceManager::userHasGlobalPermission($this->user, 'admin');

        $this->show_resource_actions = (
            ResourceManager::userHasGlobalPermission($this->user, 'autor')
            || ResourceManager::userHasResourcePermissions($this->user, 'autor')
        );
        $this->show_admin_actions = (
            $this->user_is_global_resource_admin
            || ResourceManager::userHasResourcePermissions($this->user)
        );
        $this->show_global_admin_actions = $this->user_is_global_resource_admin;
    }

    public function index_action()
    {
        if (Navigation::hasItem('/resources/overview')) {
            Navigation::activateItem('/resources/overview');
        }

        $sufficient_permissions = (
            $this->user_is_global_resource_user
            || ResourceManager::userHasResourcePermissions($this->user, 'user')
        );
        if (!$sufficient_permissions) {
            throw new AccessDeniedException();
        }

        if (!$this->show_admin_actions) {
            $this->redirect($this->action_url('rooms'));
            return;
        }

        PageLayout::setTitle(_('Übersicht'));

        if (Navigation::hasItem('/resources/overview/index')) {
            Navigation::activateItem('/resources/overview/index');
        }

        $sidebar = Sidebar::get();

        $room_search = new RoomSearch();
        $room_search->setAdditionalPropertyFormat('');
        $search = new SearchWidget($this->indexURL());
        $search->addNeedle(
            _('Suche'),
            'tree_selected_resource',
            true,
            $room_search,
            "function(room_id) {STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/resources/room/index/' + room_id));}"
        );
        $sidebar->addWidget($search);

        $tree_selected_resource = null;
        if ($this->user_is_global_resource_admin) {

            $locations = Location::findAll();
            if ($locations) {
                $sidebar->addWidget(
                    new ResourceTreeWidget($locations)
                );
            }

            $tree_selected_resource = Request::get('tree_selected_resource');
            if ($tree_selected_resource) {
                //A resource has been selected: render its index page:
                $resource = Resource::find($tree_selected_resource);
                if ($resource) {
                    $resource = $resource->getDerivedClassInstance();
                    if ($resource) {
                        if($resource instanceof Room) {
                            $this->relocate(
                                $resource->getActionURL('booking_plan')
                            );
                        } else {
                            $this->redirect(
                                $resource->getActionURL('show')
                            );
                        }
                    }
                }
            }
        }
        $this->room_requests_activated = Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS;
        $this->display_current_requests = false;
        if (!$tree_selected_resource && $this->room_requests_activated) {
            if (Config::get()->RESOURCES_DISPLAY_CURRENT_REQUESTS_IN_OVERVIEW) {
                $this->display_current_requests = true;

                //Load a list with the current room requests:
                if ($this->user_is_global_resource_admin) {
                    //Global resource admins can see all room requests.
                    //Get the 10 latest requests:
                    $room_requests = RoomRequest::findBySql(
                        "resource_requests.closed = 0
                        ORDER BY chdate DESC
                        LIMIT 10"
                    );
                } else {
                    //Users who aren't global resource admins see only the requests
                    //of the rooms where they have at least 'autor' permissions.
                    $rooms = RoomManager::getUserRooms(
                        $this->user,
                        'autor'
                    );
                    $room_ids = [];
                    foreach ($rooms as $room) {
                        $room_ids[] = $room->id;
                    }

                    $room_requests = RoomRequest::findBySql(
                        "INNER JOIN resources
                        ON resource_requests.resource_id = resources.id
                        INNER JOIN resource_categories
                        ON resources.category_id = resource_categories.id
                        WHERE
                        resource_requests.resource_id IN ( :room_ids )
                        AND
                        resource_categories.class_name IN ( :room_class_names )
                        AND
                        resource_requests.closed = 0
                        ORDER BY chdate DESC
                        LIMIT 10",
                        [
                            'room_ids' => $room_ids,
                            'room_class_names' => RoomManager::getAllRoomClassNames()
                        ]
                    );
                }
                $this->room_requests = SimpleCollection::createFromArray($room_requests)
                    ->filter(function (RoomRequest $room_request) {
                        return !$room_request->getEndDate()
                            || $room_request->getEndDate()->getTimestamp() > time();
                    });
            }
        }
    }

    public function locations_action()
    {
        if (Navigation::hasItem('/resources/overview')) {
            Navigation::activateItem('/resources/overview');
        }

        if (Navigation::hasItem('/resources/overview/locations')) {
            Navigation::activateItem('/resources/overview/locations');
        }

        PageLayout::setTitle(
            _('Übersicht über alle Standorte')
        );

        //Check permissions:
        if (!$this->user_is_global_resource_admin) {
            throw new AccessDeniedException();
        }

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neuer Standort'),
            $this->url_for('resources/location/select_category'),
            Icon::create('add'),
            ['data-dialog' => 'size=auto']
        );
        Sidebar::get()->addWidget($actions);

        $this->locations = Location::findAll();

        if (!$this->locations) {
            PageLayout::postInfo(_('Es wurden keine Standorte gefunden!'));
        }

    }

    public function buildings_action()
    {
        if (Navigation::hasItem('/resources/overview')) {
            Navigation::activateItem('/resources/overview');
        }
        if (Navigation::hasItem('/resources/overview/buildings')) {
            Navigation::activateItem('/resources/overview/buildings');
        }

        PageLayout::setTitle(_('Übersicht über alle Gebäude'));

        //Check permissions:
        if (!$this->user_is_global_resource_admin) {
            throw new AccessDeniedException();
        }

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neues Gebäude'),
            URLHelper::getURL(
                'dispatch.php/resources/building/select_category'
            ),
            Icon::create('add'),
            ['data-dialog' => 'size=auto']
        );
        Sidebar::get()->addWidget($actions);

        $this->buildings = Building::findAll();

        if (!$this->buildings) {
            PageLayout::postInfo(
                _('Es wurden keine Gebäude gefunden!')
            );
        }

        $this->building_ids = [];
        $building_names = [];

        if (Request::submitted('create_clipboards')) {
            $this->building_ids = Request::getArray('building_ids');

            foreach ($this->building_ids as $building_id) {
                $building = Building::find($building_id);
                if (!$building) {
                    continue;
                }

                $rooms = $building->rooms;

                if (!$rooms) {
                    //The building has no rooms:
                    //There is no need to create empty clipboards.
                    continue;
                }

                $clipboard = new Clipboard();
                $clipboard->user_id = $this->user->id;
                $clipboard->name = $building->getFullName();
                $clipboard->allowed_item_class = 'Room';
                $clipboard->store();

                foreach ($rooms as $room) {
                    $clipboard->addItem($room->id, 'Room');
                }

                $building_names[] = $building->name;
            }

            $building_names = implode(', ', $building_names);

            if ($this->building_ids) {
                PageLayout::postSuccess(
                    sprintf(
                        ngettext(
                            'Die Raumgruppe für das Gebäude %s wurde angelegt!',
                            'Die Raumgruppen für die Gebäude %s wurden angelegt!',
                            count($this->building_ids)
                        ),
                        htmlReady($building_names)
                    )
                );
            }
        } elseif (Request::submitted('room_permissions')) {
            $this->building_ids = Request::optionArray('building_ids');

            $room_ids = [];

            Building::findEachMany(
                function (Building $building) use (&$room_ids) {
                    foreach ($building->rooms as $room) {
                        $room_ids[] = $room->id;
                    }
                },
                $this->building_ids
            );

            if ($room_ids) {
                //Redirect to the permissions dialog:
                $this->redirect(
                    'resources/room_group/permissions',
                    ['room_ids' => $room_ids]
                );
            } else {
                PageLayout::postInfo(_('Das Gebäude hat keine Räume.'));
                $this->buildings = [];
            }
        }
    }

    public function rooms_action()
    {
        if ($this->user_is_global_resource_user) {
            PageLayout::setTitle(_('Übersicht über alle Räume'));
        } else {
            PageLayout::setTitle(_('Meine Räume'));
        }

        if (Navigation::hasItem('/resources/overview')) {
            Navigation::activateItem('/resources/overview');
        }
        if (Navigation::hasItem('/resources/overview/rooms')) {
            Navigation::activateItem('/resources/overview/rooms');
        }

        //Check permissions:
        $sufficient_permissions = (
            $this->user_is_global_resource_user
            || ResourceManager::userHasResourcePermissions($this->user, 'user')
        );
        if (!$sufficient_permissions) {
            throw new AccessDeniedException();
        }

        //build sidebar:
        $sidebar = Sidebar::get();

        if ($this->user_is_global_resource_admin) {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Raum hinzufügen'),
                URLHelper::getURL(
                    'dispatch.php/resources/room/select_category'
                ),
                Icon::create('add'),
                ['data-dialog' => 'size=auto']
            );
            $sidebar->addWidget($actions);
        }

        $clipboard = new RoomClipboardWidget();
        $sidebar->addWidget($clipboard);

        $search = new SearchWidget($this->roomsURL());
        $search->setTitle(_('Raumsuche'));
        $search->addNeedle(_('Gebäude oder Raum'), 'building_room_name', true);

        $sidebar->addWidget($search);

        // search for all rooms
        $rooms_sql = "INNER JOIN resource_categories rc
                      ON resources.category_id = rc.id
                      INNER JOIN resources pr
                      ON resources.parent_id = pr.id
                      WHERE rc.class_name IN ( :room_class_names )";
        // narrow down rooms according to search parameter (room or building name)
        $rooms_sql_with_request = $rooms_sql .
                     "AND (resources.name LIKE CONCAT('%', :room_name, '%')
                      OR pr.name LIKE CONCAT('%', :building_name, '%'))";

        $rooms_parameter['room_class_names'] = RoomManager::getAllRoomClassNames();
        $rooms_parameter['room_name'] = Request::get('building_room_name');
        $rooms_parameter['building_name'] = Request::get('building_room_name');

        if ($this->user_is_global_resource_user) {
            if (Request::get('building_room_name')) {
                $rooms_sql_with_request .= " ORDER BY sort_position DESC, name ASC, mkdate ASC";
                $this->rooms = Room::findBySQL($rooms_sql_with_request, $rooms_parameter);
            } else {
                $this->rooms = Room::findAll();
            }
        } else {
            //Get only the locations for which
            //the user has at least user permissions:
            $rooms_parameter['user_id'] = $this->user->id;
            $rooms_parameter['now']     = time();

            // did the user search for a specific room or building name?
            $rooms_sql = Request::get('building_room_name') ? $rooms_sql_with_request : $rooms_sql;

            $rooms_sql .= " AND resources.id IN (
                            SELECT resource_id
                            FROM resource_permissions
                            WHERE
                            user_id = :user_id
                            UNION
                            SELECT resource_id
                            FROM resource_temporary_permissions
                            WHERE
                            user_id = :user_id
                            AND
                            begin <= :now
                            AND
                            end >= :now
                        )
                        ORDER BY sort_position DESC, name ASC, mkdate ASC";

            $this->rooms = Room::findBySql($rooms_sql, $rooms_parameter);
        }

        if (!$this->rooms) {
            PageLayout::postInfo(_('Es wurden keine Räume gefunden!'));
        }
    }


    /**
     * Get a list of all rooms with have the property
     * booking_plan_is_public set.
     */
    public function public_booking_plans_action()
    {
        if (Navigation::hasItem('/resources/overview/public_booking_plans')) {
            Navigation::activateItem('/resources/overview/public_booking_plans');
        }
        if (Navigation::hasItem('/public_booking_plans')) {
            Navigation::activateItem('/public_booking_plans');
        }

        PageLayout::setTitle(_('Öffentlich zugängliche Raumpläne'));

        $rooms = Room::findByPublicBookingPlans();

        if (!$rooms) {
            PageLayout::postInfo(
                _('Es sind keine öffentlich zugänglichen Raumpläne vorhanden!')
            );
        }

        $this->grouped_rooms = RoomManager::groupRooms($rooms);
    }


    /**
     * Displays the whole structure of the room management system.
     */
    public function structure_action()
    {
        if (Navigation::hasItem('/resources/structure')) {
            Navigation::activateItem('/resources/structure');
        }

        PageLayout::setTitle(_('Strukturansicht'));

        if (!RoomManager::userHasRooms($this->user, 'user', true) && !$this->user_is_global_resource_user) {
            throw new AccessDeniedException();
        }

        $this->locations = Location::findAll();
        if (!$this->locations) {
            PageLayout::postInfo(
                _('Es sind keine Standorte in Stud.IP vorhanden!')
            );
        }
    }
}
