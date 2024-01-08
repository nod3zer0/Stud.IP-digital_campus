<?php
# Lifter010: TODO
/**
 * room_requests.php - administration of room requests
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      André Noack <noack@data-quest.de>
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @author      Michaela Brückner <brueckner@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 */

class Course_RoomRequestsController extends AuthenticatedController
{
    /**
     * Common tasks for all actions
     *
     * @param string $action Called action
     * @param array  $args   Possible arguments
     */
    public function before_filter(&$action, &$args)
    {
        $this->current_action = $action;

        parent::before_filter($action, $args);

        $this->current_user = User::findCurrent();
        $this->user_is_global_resource_admin = ResourceManager::userHasGlobalPermission(
            $this->current_user,
            'admin'
        );
        $this->course_id = Request::option('cid', $args[0] ?? null);
        $pagetitle = '';
        //Navigation in der Veranstaltung:
        if (Navigation::hasItem('/course/admin/room_requests')) {
            Navigation::activateItem('/course/admin/room_requests');
        }

        if (!get_object_type($this->course_id, ['sem']) ||
            SeminarCategories::GetBySeminarId($this->course_id)->studygroup_mode ||
            !$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)
        ) {
            throw new Trails_Exception(400);
        }

        PageLayout::setHelpKeyword('Basis.VeranstaltungenVerwaltenAendernVonZeitenUndTerminen');
        $pagetitle .= Course::find($this->course_id)->getFullname() . ' - ';
        $pagetitle .= _('Verwalten von Raumanfragen');
        PageLayout::setTitle($pagetitle);

        $this->available_room_categories = ResourceCategory::findByClass_name(Room::class);
        $this->step = 0;
        $this->max_preparation_time = Config::get()->RESOURCES_MAX_PREPARATION_TIME;
    }

    /**
     * Display the list of room requests
     */
    public function index_action()
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }
        $this->url_params = [];
        if (Request::get('origin') !== null) {
            $this->url_params['origin'] = Request::get('origin');
        }

        $this->room_requests = RoomRequest::findBySQL(
            'course_id = :course_id
            ORDER BY course_id, metadate_id, termin_id',
            [
                'course_id' => $this->course_id
            ]
        );
        $this->request_id = Request::option('request_id');

        $actions = new ActionsWidget();
        $actions->addLink(
            _('Neue Raumanfrage erstellen'),
            $this->url_for('course/room_requests/new'),
            Icon::create('add', 'clickable')
        );
        Sidebar::get()->addWidget($actions);

        if ($GLOBALS['perm']->have_studip_perm('admin', $this->course_id)) {
            $widget = new CourseManagementSelectWidget();
            Sidebar::Get()->addWidget($widget);
        }
    }


    /**
     * Show information about a request
     *
     * @param String $request_id Id of the request
     */
    public function info_action($request_id)
    {
        $request = RoomRequest::find($request_id);
        $this->request = $request;
        $this->render_template('course/room_requests/_request.php', null);
    }

    /**
     * Start point to creating a new request
     */
    public function new_request_action($request_id = '')
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        Helpbar::get()->addPlainText(
            _('Information'),
            _('Hier können Sie Angaben zu gewünschten Raumeigenschaften machen.')
        );

        $this->request_id = $request_id;

        if (Request::submitted('request_id')) {
            $this->request_id = Request::get('request_id');
        }
        if (!$this->request_id) {
            $this->request_id = md5(uniqid('RoomRequest'));
        }

        // e.g. cycle, course, date
        $this->request_range = Request::get('range_str');

        // multiple dates
        $this->request_range_ids = Request::getArray('range_ids') ?: $_SESSION[$this->request_id]['range_ids'] ?? [];
        // a single date or whole course
        $this->request_range_id = Request::get('range_id', Context::getId());

        if (!isset($_SESSION[$this->request_id])) {
            $_SESSION[$this->request_id] = [];
        }
        $_SESSION[$this->request_id]['range'] = $this->request_range ?: $_SESSION[$this->request_id]['range'] ?? null;
        $_SESSION[$this->request_id]['range_ids'] = $this->request_range_ids ?: [$this->request_range_id];
        $_SESSION[$this->request_id]['search_by'] = '';
        $_SESSION[$this->request_id]['room_category_id'] = '';
        $_SESSION[$this->request_id]['room_id'] = '';
        $_SESSION[$this->request_id]['room_name'] = '';
        $_SESSION[$this->request_id]['selected_properties'] = [];

        $this->request = null;

        // look for existing request or create a new one
        $this->request = new RoomRequest($this->request_id);
        // time ranges (start date, end date)
        $this->request->setRangeFields($_SESSION[$this->request_id]['range'], $_SESSION[$this->request_id]['range_ids']);
        $this->request_time_intervals = $this->request->getTimeIntervals();

    }

    /**
     * Step 1: Either selecting a room category or searching for a room name initially
     * @param String $request_id ID of the request
     */
    public function request_first_step_action($request_id)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->step = 1;

            $this->category_id = Request::get('category_id');
            $this->search_by_category = Request::submitted('search_by_category');
            if (!isset($_SESSION[$request_id])) {
                $_SESSION[$request_id] = [];
            }
            $_SESSION[$request_id]['room_category_id'] = $this->category_id;

            $this->room_name = Request::get('room_name');
            $this->search_by_roomname = Request::submitted('search_by_name');
            $_SESSION[$request_id]['room_name'] = $this->room_name;

            // user selects a room category OR enters a room name
            if ($this->category_id !== null && $this->search_by_category) {
                $_SESSION[$request_id]['search_by'] = 'category';
                $this->redirect(
                    'course/room_requests/request_find_available_properties/' . $this->request_id . '/' . $this->step . '/category'
                );
            } elseif ($this->room_name && $this->search_by_roomname) {
                $_SESSION[$request_id]['search_by'] = 'roomname';
                $this->redirect(
                    'course/room_requests/request_find_matching_rooms/' . $this->request_id . '/' . $this->step . '/roomname'
                );
            } else {
                $this->redirect(
                    'course/room_requests/new_request/' . $this->request_id
                );
            }
        }

    }

    /**
     * Searching for (a) matching room(s) via room name, e.g. 'hör%'
     * @param String $request_id ID of the request
     * @param String $step
     * @return void
     */
    public function request_find_matching_rooms_action($request_id, $step)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;
        $this->step = (int)$step;
        $this->room_name = $_SESSION[$request_id]['room_name'];

        $this->request = new RoomRequest($this->request_id);
        $this->request->setRangeFields($_SESSION[$this->request_id]['range'], $_SESSION[$this->request_id]['range_ids']);

        $search_properties = $_SESSION[$request_id]['selected_properties'] ?? [];

        if (!empty($_SESSION[$request_id]['room_category_id'])) {
            $search_properties['room_category_id'] = $_SESSION[$request_id]['room_category_id'];
        }

        if (!empty($search_properties['seats'])) {
            //The seats property value is a minimum.
            $search_properties['seats'] = [
                $search_properties['seats'],
                null
            ];
        }

        // find rooms matching to selected properties
        $this->available_rooms = RoomManager::findRooms(
            $this->room_name,
            null,
            null,
            $search_properties,
            [],
            'name ASC, mkdate ASC'
        );

        // small icons in front of room name to show whether they are bookable or not
        $this->available_room_icons = $this->getRoomBookingIcons($this->available_rooms);

        // selected room and its category
        $this->selected_room = Resource::find($_SESSION[$request_id]['room_id'] ?: $this->request->resource_id);

        $this->selected_room_category_id = $this->selected_room->category_id ?? $_SESSION[$request_id]['room_category_id'] ?? null;

        $_SESSION[$request_id]['room_category_id'] = $_SESSION[$request_id]['room_category_id'] ?? $this->selected_room->category_id ?? null;

        // after selecting a room, go to next step or stay here if no room was selected at all
        if (Request::submitted('select_room')) {
            $this->selected_room_id = Request::get('selected_room_id');
            $_SESSION[$request_id]['room_id'] = $this->selected_room_id;
            $_SESSION[$request_id]['select_room'] = true;

            $this->redirect(
                'course/room_requests/request_check_properties/' . $this->request_id
            );
            return;
        }

        // we might also search for new rooms and stay within step 1
        else if (Request::get('room_name') && Request::submitted('search_by_name')) {
            $_SESSION[$request_id]['room_name'] = Request::get('room_name');
            $this->redirect(
                'course/room_requests/request_find_matching_rooms/' . $this->request_id . '/' . $this->step
            );
            return;
        }

        // or we filter via category
        else if (Request::get('category_id') && Request::submitted('select_properties')) {
            $_SESSION[$request_id]['search_by'] = 'category';
            $_SESSION[$request_id]['room_category_id'] = Request::get('category_id');
            $this->redirect(
                'course/room_requests/request_find_available_properties/' . $this->request_id . '/' . $this->step
            );
            return;
        } else if (Request::submitted('reset_category')) {
            //Delete all selected properties from the session since the category is reset
            $_SESSION[$request_id]['selected_properties'] = [];
            $_SESSION[$request_id]['room_category_id'] = '';
            $_SESSION[$request_id]['room_name'] = '';
            $_SESSION[$request_id]['room_id'] = '';
            $this->redirect('course/room_requests/request_find_available_properties/' . $this->request_id . '/1');
            return;
        }

        // for step 2: after choosing a specific room OR searching via properties
        if ($this->step === 2) {
            if ($_SESSION[$request_id]['search_by'] == 'roomname') {
                // find category via room
                $this->category = ResourceCategory::find($this->selected_room_category_id);
                if ($this->category) {
                    $this->available_properties = $this->category->getRequestableProperties();
                }

                $this->selected_properties = $_SESSION[$request_id]['selected_properties'];
                $this->room = Room::find($_SESSION[$request_id]['room_id']);
                $this->selected_properties['seats'] = $_SESSION[$request_id]['selected_properties']['seats']
                    ?: $this->course->admission_turnout
                    ?: Config::get()->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS;
                $_SESSION[$request_id]['selected_properties']['seats'] = $this->selected_properties['seats'];
            } else if ($_SESSION[$request_id]['search_by'] === 'category') {
                $this->room = Room::find($_SESSION[$request_id]['room_id']);
                if ($this->room) {
                    $this->grouped_properties = $this->room->getGroupedProperties();
                    foreach ($this->grouped_properties as $properties) {
                        foreach ($properties as $property) {
                            $this->selected_properties[$property->name] = $property->state;
                        }
                    }
                }

            }

            // find rooms fitting to category and properties
            if (Request::submitted('search_rooms')) {
                $this->selected_properties = Request::getArray('selected_properties');
                $_SESSION[$request_id]['selected_properties'] = $this->selected_properties;

                // no min number of seats
                if (
                    (!$_SESSION[$request_id]['selected_properties']['seats'] || $_SESSION[$request_id]['selected_properties']['seats'] < 1)
                    && $_SESSION[$request_id]['search_by'] === 'category'
                ) {
                    PageLayout::postError(
                        _('Die Mindestanzahl der Sitzplätze beträgt 1!')
                    );
                    $this->redirect(
                        'course/room_requests/request_find_matching_rooms/' . $request_id . '/' . $this->step
                    );
                    return;
                } else {
                    $this->redirect(
                        'course/room_requests/request_find_matching_rooms/' . $request_id . '/' . $this->step
                    );
                    return;
                }
            }

            // let's find all the properties belonging to the selected category
            $this->room_category_id = $_SESSION[$request_id]['room_category_id'];
            $this->category = ResourceCategory::find($this->room_category_id);
            $this->available_properties = $this->category->getRequestableProperties();

            // properties, like 'Sitzplätze', 'behindertengerecht' etc
            $this->selected_properties = $_SESSION[$request_id]['selected_properties'] ?? null;
            $this->preparation_time = $_SESSION[$request_id]['preparation_time'] ?? null;
            $this->comment = $_SESSION[$request_id]['comment'] ?? null;
            $this->request->category_id = $_SESSION[$request_id]['room_category_id'];

            // finally we want to show a summary
            if (Request::submitted('show_summary')) {
                $this->selected_room_id = Request::get('selected_room_id');
                $_SESSION[$request_id]['room_id'] = $this->selected_room_id;
                $_SESSION[$request_id]['selected_properties'] = Request::getArray('selected_properties');
                $this->redirect('course/room_requests/request_show_summary/' . $this->request_id );
            }
        }
    }

    /**
     * Searching for (a) matching room(s) by initially selecting a room category, e.g. 'Hörsaal'
     * @param String $request_id ID of the request
     * @param String $step
     * @return void
     */
    public function request_find_available_properties_action($request_id, $step)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;
        $this->step = (int)$step;

        $this->request = new RoomRequest($this->request_id);
        $this->request->setRangeFields($_SESSION[$this->request_id]['range'], $_SESSION[$this->request_id]['range_ids']);

        // let's find all the properties belonging to the selected category
        $this->room_category_id = $_SESSION[$request_id]['room_category_id'] ?: $this->request->category_id;
        $this->room_name = $_SESSION[$request_id]['room_name'];
        $this->selected_room = Resource::find($_SESSION[$request_id]['room_id'] ?: $this->request->resource_id);
        $this->category = $this->room_category_id ? ResourceCategory::find($this->room_category_id) : '';
        $this->available_properties = $this->room_category_id ? $this->category->getRequestableProperties() : '';
        $this->selected_properties = $_SESSION[$request_id]['selected_properties'];

        $this->course = Course::find($this->course_id);
        $this->selected_properties['seats'] = $_SESSION[$request_id]['selected_properties']['seats']
            ?? $this->course->admission_turnout
            ?: Config::get()->RESOURCES_ROOM_REQUEST_DEFAULT_SEATS;

        $this->preparation_time = $_SESSION[$request_id]['preparation_time'] ?? null;
        $this->comment = $_SESSION[$request_id]['comment'] ?? null;

        // when searching for a room name, list found room
        if ($_SESSION[$request_id]['room_name'] !== '') {
            $search_properties['room_category_id'] = $this->room_category_id;
            $search_properties['seats'] = [
                1,
                null
            ];

            $this->available_rooms = RoomManager::findRooms(
                $this->room_name,
                null,
                null,
                $search_properties,
                [],
                'name ASC, mkdate ASC'
            );

            // small icons in front of room name to show whether they are bookable or not
            $this->available_room_icons = $this->getRoomBookingIcons($this->available_rooms);
        }

    }

    /**
     * Check desired properties for a room category to go to step 2
     * @param String $request_id ID of the request
     * @return void
     *
     */
    public function request_check_properties_action($request_id)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;

        // select a room, search for a room name or search for rooms matching properties
        if (Request::submitted('select_room')) {
            $this->selected_room_id = Request::get('selected_room_id');
            $_SESSION[$request_id]['room_id'] = $this->selected_room_id;
            $_SESSION[$request_id]['select_room'] = true;
            $this->step = 2;
            $this->request = new RoomRequest($this->request_id);
            $this->redirect(
                'course/room_requests/request_find_matching_rooms/' . $this->request_id . '/' . $this->step
            );
        } else if (Request::get('room_name') && Request::submitted('search_by_name')) {
            $this->selected_properties = Request::getArray('selected_properties');
            $this->category_id = Request::get('category_id');
            $_SESSION[$request_id]['selected_properties'] = $this->selected_properties;
            $_SESSION[$request_id]['room_category_id'] = $this->category_id;
            $_SESSION[$request_id]['comment'] = $this->comment;
            $_SESSION[$request_id]['room_name'] = Request::get('room_name');
            $this->request = new RoomRequest($this->request_id);
            $this->redirect(
                'course/room_requests/request_find_available_properties/' . $this->request_id . '/1/category'
            );

        } else if (Request::submitted('search_rooms')) {
            $this->selected_properties = Request::getArray('selected_properties');
            $this->category_id = Request::get('category_id');
            $_SESSION[$request_id]['room_category_id'] = $this->category_id;
            $_SESSION[$request_id]['selected_properties'] = $this->selected_properties;
            $_SESSION[$request_id]['room_name'] = '';

            // no min number of seats
            if (
                (!$_SESSION[$request_id]['selected_properties']['seats'] || $_SESSION[$request_id]['selected_properties']['seats'] < 1)
                && $_SESSION[$request_id]['search_by'] === 'category'
            ) {
                PageLayout::postError(
                    _('Die Mindestanzahl der Sitzplätze beträgt 1!')
                );

                $this->redirect(
                    'course/room_requests/request_find_available_properties/' . $request_id . '/1/category'
                );
            } else {
                $this->step = 2;
                $this->request = new RoomRequest($this->request_id);
                $this->redirect(
                        'course/room_requests/request_find_matching_rooms/' . $this->request_id . '/' . $this->step
                    );
            }
        } else if (Request::submitted('reset_category')) {
            //Delete all selected properties from the session since the category is reset
            $_SESSION[$request_id]['selected_properties'] = [];
            $_SESSION[$request_id]['room_category_id'] = '';
            $this->redirect('course/room_requests/request_find_available_properties/' . $this->request_id . '/1');
        } else if (Request::submitted('search_by_category')) {
            if (Request::get('category_id') === '0') {
                $_SESSION[$request_id]['room_category_id'] = '';
            } else {
                $_SESSION[$request_id]['room_category_id'] = Request::get('category_id');
            }

            $this->redirect(
                'course/room_requests/request_find_available_properties/' . $this->request_id . '/1'  . '/category'
            );
        } else if (Request::submitted('show_summary')) {
            $this->request = new RoomRequest($this->request_id);
            $this->selected_properties = Request::getArray('selected_properties');

            $_SESSION[$request_id]['selected_properties'] = $this->selected_properties;
            $this->selected_room_id = Request::get('selected_room_id');
            $_SESSION[$request_id]['room_id'] = $this->selected_room_id;

            $this->redirect('course/room_requests/request_show_summary/' . $this->request_id  );
        } else {
            $this->step = 2;
            $this->request = new RoomRequest($this->request_id);
            $this->redirect(
                'course/room_requests/request_find_matching_rooms/' . $this->request_id . '/' . $this->step
            );
        }

    }

    /**
     * Show a summary of all request properties before storing; we have the possibility of going back and
     * editing if necessary. This action is also used for editing a request via action menu
     * @param String $request_id ID of the request
     * @return void
     *
     */
    public function request_show_summary_action($request_id)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;
        $this->step = 3;

        if (Request::submitted('clear_cache')) {
            $_SESSION[$request_id] = [];
        }

        $this->request = new RoomRequest($this->request_id);
        $this->request->setRangeFields(
            $_SESSION[$this->request_id]['range'] ?? '',
            $_SESSION[$this->request_id]['range_ids'] ?? []
        );

        $this->selected_room_category = ResourceCategory::find($_SESSION[$request_id]['room_category_id'] ?? $this->request->category_id);

        $this->selected_room = Resource::find($_SESSION[$request_id]['room_id'] ?? $this->request->resource_id);

        $this->room_id = $_SESSION[$request_id]['room_id'] ?? $this->request->resource_id;
        $this->available_properties = $this->selected_room_category->getRequestableProperties();

        $this->selected_properties = $_SESSION[$request_id]['selected_properties'] ?? [];
        $this->request_properties = $this->request->properties;

        // either properties from stored request or those from session
        if ($this->request_properties && empty($_SESSION[$request_id]['selected_properties'])) {
            foreach ($this->request_properties as $property) {
                $this->selected_properties[$property->name] = $property->state;
            }
            $_SESSION[$request_id]['selected_properties'] = $this->selected_properties;
        }

        $this->preparation_time = intval($this->request->preparation_time / 60);
        $this->reply_lecturers = $this->request->reply_recipients === ResourceRequest::REPLY_LECTURER;
        $this->comment = $this->request->comment;

        $_SESSION[$request_id]['search_by'] = $this->selected_room ? 'roomname' : 'category';
        $_SESSION[$request_id]['room_category_id'] = $this->selected_room_category->id;
        $_SESSION[$request_id]['room_id'] = $this->selected_room->id;
    }

    public function store_request_action($request_id)
    {
        if (!Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) {
            throw new AccessDeniedException(
                _('Das Erstellen von Raumanfragen ist nicht erlaubt!')
            );
        }

        $this->request_id = $request_id;
        $this->request = new RoomRequest($this->request_id);
        $this->request->setRangeFields($_SESSION[$this->request_id]['range'], $_SESSION[$this->request_id]['range_ids']);

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            $this->request->user_id = $this->current_user->id;
            $this->preparation_time = Request::int('preparation_time', 0);
            $this->request->preparation_time = $this->preparation_time * 60;
            $this->request->comment = Request::get('comment');

            if (Request::get('reply_lecturers')) {
                $this->request->reply_recipients = ResourceRequest::REPLY_LECTURER;
            } else {
                $this->request->reply_recipients = ResourceRequest::REPLY_REQUESTER;
            }
            $this->request->category_id = $_SESSION[$request_id]['room_category_id'] ?: $this->request->category_id;

            $this->request->resource_id = $_SESSION[$request_id]['room_id'] ?: $this->request->resource_id;
            $this->request->course_id = Context::getId();
            $this->request->last_modified_by = $this->current_user->id;

            $this->request->store();

            //Store the properties:
            foreach ($_SESSION[$request_id]['selected_properties'] as $name => $state) {
                $this->request->setProperty($name, $state);
            }

            // once stored, we can delete the session data for this request
            $_SESSION[$this->request_id] = [];

            PageLayout::postSuccess(_('Die Anfrage wurde gespeichert!'));
            $this->relocate('course/timesrooms/');
        }
    }

    /**
     * Store a request and its properties
     * @param string $request ID of the request
     * @param array $properties desired properties
     * @return void
     */
    private function storeRequest($request, $properties)
    {
        // once stored, we can delete the session data for this request
        $request->store();
        $_SESSION[$request->id] = [];

        //Store the properties:
        foreach ($properties as $name => $state) {
            $request->setProperty($name, $state);
        }
    }

    private function getRoomBookingIcons($available_rooms)
    {
        $this->available_room_icons = [];

        $request_time_intervals = $this->request->getTimeIntervals();

        foreach ($available_rooms as $room) {
            $request_dates_booked = 0;
            foreach ($request_time_intervals as $interval) {
                $booked = ResourceBookingInterval::countBySql(
                    'resource_id = :room_id AND begin < :end AND end > :begin',
                    [
                        'room_id' => $room->id,
                        'begin' => $interval['begin'],
                        'end' => $interval['end']
                    ]
                ) > 0;
                if ($booked) {
                    $request_dates_booked++;
                }
            }
            if ($request_dates_booked === 0) {
                $this->available_room_icons[$room->id] =
                    Icon::create('check-circle', Icon::ROLE_STATUS_GREEN)->asImg(
                        [
                            'class' => 'text-bottom',
                            'title' => _('freier Raum')
                        ]
                    );
                $available_rooms[] = $room;
            } elseif ($request_dates_booked < $request_time_intervals) {
                $this->available_room_icons[$room->id] = Icon::create('exclaim-circle', Icon::ROLE_STATUS_YELLOW)->asImg([
                    'class' => 'text-bottom',
                    'title' => _('teilweise belegter Raum')
                ]);
                $available_rooms[] = $room;
            }
        }
        return $this->available_room_icons;
    }

    /**
     * delete one room request
     */
    public function delete_action($request_id)
    {
        $request = RoomRequest::find($request_id);
        if (!$request) {
            throw new Trails_Exception(403);
        }
        if (Request::isGet()) {
            PageLayout::postQuestion(sprintf(
                _('Möchten Sie die Raumanfrage "%s" löschen?'),
                htmlReady($request->getTypeString())), $this->url_for('course/room_requests/delete/' . $request_id));
        } else {
            CSRFProtection::verifyUnsafeRequest();
            if (Request::submitted('yes')) {
                if ($request->delete()) {
                    PageLayout::postSuccess("Die Raumanfrage wurde gelöscht.");
                }
            }
        }
        $this->redirect('course/timesrooms/index');
    }
}
