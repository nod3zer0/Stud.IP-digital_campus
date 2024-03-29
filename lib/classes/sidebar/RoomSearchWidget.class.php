<?php


/**
 * A special search widget that provides a room search.
 */
class RoomSearchWidget extends SidebarWidget
{
    protected $action_link;
    protected $criteria;
    protected $selected_criteria;
    protected $defined_properties;
    protected $semesters;

    protected function setupSearchParameters()
    {
        $this->semesters = array_reverse(Semester::getAll());
        $this->defined_properties = RoomManager::getAllRoomPropertyDefinitions(
            true,
            [
                'seats', 'room_type','room_category_id'
            ]
        );

        $resource_categories = ResourceCategory::findBySQL("class_name = 'Room' ORDER by name");
        $categories = [
            '' => _('Alle Kategorien')
        ];
        if($resource_categories) {
            foreach($resource_categories as $resource_category) {
                $categories[$resource_category->id] = $resource_category->name;
            }
        }

        $room_types = Room::getAllRoomTypes();
        if (!empty($room_types)) {
            $filtered_room_types = [];
            foreach ($room_types as $type) {
                $filtered_room_types[$type] = $type;
            }
            $room_types = array_merge(
                ['' => _('Alle Raumtypen')],
                $filtered_room_types
            );
        }

        $this->criteria = [];

        if ($this->defined_properties) {
            foreach ($this->defined_properties as $property) {
                $this->criteria[$property->name] = [
                    'name' => $property->id,
                    'title' => (
                        $property->display_name != ''
                        ? $property->display_name
                        : $property->name
                    ),
                    'type' => $property->type,
                    'range_search' => $property->range_search,
                    'optional' => true
                ];
                if ($property->type === 'select') {
                    $this->criteria[$property->name]['options'] = $property->getOptionsArray();
                }
            }
        }

        //Add special criteria:
        $this->criteria['special__room_name'] = [
            'name' => 'special__room_name',
            'title' => _('Raumname'),
            'type' => 'text',
            'range_search' => false,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];
        $this->criteria['room_category_id'] = [
            'name' => 'room_category_id',
            'title' => _('Kategorie'),
            'type' => 'select',
            'range_search' => false,
            'options' => $categories,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];
        if (!empty($room_types)) {
            $this->criteria['room_type'] = [
                'name'         => 'room_type',
                'title'        => _('Raumtyp'),
                'type'         => 'select',
                'range_search' => false,
                'options'      => $room_types,
                'switch'       => false,
                'value'        => '',
                'optional'     => false
            ];
        }
        $this->criteria['special__building_location'] = [
            'name' => 'special__building_location',
            'title' => _('Standort / Gebäude'),
            'type' => 'hidden',
            'range_search' => false,
            'switch' => false,
            'value' => '',
            'optional' => false
        ];

        if (Request::get('special__building_location') && !Request::submitted('room_search_reset')) {
            $res_id = explode('_', Request::get('special__building_location'));
            $selected_res =Resource::find($res_id[1]);
            if ($selected_res) {
                $this->criteria['special__building_location_label'] = [
                    'name' => 'special__building_location_label',
                    'title' => _('Standort / Gebäude'),
                    'type' => 'disabled_text',
                    'range_search' => false,
                    'switch' => false,
                    'value' => $selected_res->name,
                    'optional' => false
                ];
            }
        }

        $current_semester = Semester::findCurrent();
        $begin = new DateTime();
        $begin = $begin->setTimestamp($current_semester->beginn);
        $begin->setTime(intval(date('H')), 0, 0);
        $end = clone $begin;
        $end = $end->setTimestamp($current_semester->ende);

        $this->criteria['special__time_range'] = [
            'name' => 'special__time_range',
            'title' => _('Frei in einem Zeitbereich'),
            'optional' => false,
            'enabled' => false,
            'semester' => [
                'value' => $current_semester->id
            ],
            'range' => [
                'begin' => $begin,
                'end' => $end
            ],
            'day_of_week' => [
                'options' => [
                    '1' => _('Montag'),
                    '2' => _('Dienstag'),
                    '3' => _('Mittwoch'),
                    '4' => _('Donnerstag'),
                    '5' => _('Freitag'),
                    '6' => _('Samstag'),
                    '7' => _('Sonntag')
                ],
                'value' => ''
            ]
        ];

        $this->criteria['special__seats'] = [
            'name' => 'special__seats',
            'title' => _('Sitzplätze'),
            'type' => 'num',
            'range_search' => true,
            'switch' => true,
            'value' => [10, 100],
            'optional' => false
        ];

    }


    protected function handleSearchRequest()
    {
        $this->selected_criteria = [];

        //If the reset button has been pressed, reset the search
        //and do nothing else.
        if ($this->searchResetRequested() || !$this->searchRequested()) {
            //If no room search is requested we can stop here.
            return;
        }

        $default_begin = new DateTime();
        $default_begin = $default_begin->add(new DateInterval('P1D'));
        $default_begin->setTime(intval(date('H')), 0, 0);
        $default_end = clone $default_begin;
        $default_end = $default_end->add(new DateInterval('PT30M'));

        foreach ($this->criteria as $name => $data) {
            if ($name == 'special__time_range') {
                if (Request::get($data['name'] . '_enabled')) {
                    $data['enabled'] = true;
                    $this->selected_criteria[$name] = $data;
                    if (Request::submittedSome(
                        $data['name'] . '_begin_date',
                        $data['name'] . '_begin_time',
                        $data['name'] . '_end_date',
                        $data['name'] . '_end_time'
                    )) {
                            $submitted_begin = Request::getDateTime(
                                $data['name'] . '_begin_date',
                                'd.m.Y',
                                $data['name'] . '_begin_time',
                                'H:i'
                            );
                            $submitted_end = Request::getDateTime(
                                $data['name'] . '_end_date',
                                'd.m.Y',
                                $data['name'] . '_end_time',
                                'H:i'
                            );
                            if(!$submitted_begin || !$submitted_end) {
                                $submitted_begin = $default_begin;
                                $submitted_end = $default_end;
                            }
                        $this->selected_criteria[$name]['range'] = [
                            'begin' => $submitted_begin,
                            'end' => $submitted_end
                        ];
                    }
                    $this->selected_criteria[$name]['day_of_week']['value'] =
                        Request::get($data['name'] . '_day_of_week');
                    $this->selected_criteria[$name]['semester']['value'] =
                        Request::get($data['name'] . '_semester_id');
                }
            } else {
                if (!empty($data['switch'])) {
                    if (Request::get($data['name'] . '_enabled')) {
                        $data['enabled'] = true;
                    } else {
                        //The criteria isn't enabled. We can move on to the
                        //next criteria.
                        continue;
                    }
                }
                if ($data['type'] == 'date') {
                    if ($data['range_search']) {
                        if (Request::submittedSome(
                            $data['name'] . '_begin_date',
                            $data['name'] . '_begin_time',
                            $data['name'] . '_end_date',
                            $data['name'] . '_end_time'
                        )) {
                            $this->selected_criteria[$name] = $data;
                            $submitted_begin = Request::getDateTime(
                                $data['name'] . '_begin_date',
                                'd.m.Y',
                                $data['name'] . '_begin_time',
                                'H:i'
                            );
                            $submitted_end = Request::getDateTime(
                                $data['name'] . '_end_date',
                                'd.m.Y',
                                $data['name'] . '_end_time',
                                'H:i'
                            );
                            if(!$submitted_begin || !$submitted_end) {
                                $submitted_begin = $default_begin;
                                $submitted_end = $default_end;
                            }
                            $this->selected_criteria[$name]['value'] = [
                                'begin' => $submitted_begin,
                                'end' => $submitted_end
                            ];
                        }
                    } else {
                        if (Request::submittedSome(
                            $data['name'] . '_date',
                            $data['name'] . '_time'
                        )) {
                            $this->selected_criteria[$name] = $data;
                            $this->selected_criteria[$name]['value'] =
                                Request::getDateTime(
                                    $data['name'] . '_date',
                                    'd.m.Y',
                                    $data['name'] . '_time',
                                    'H:i'
                                );
                        }
                    }
                } elseif ($data['type'] === 'num' && $data['range_search']) {
                    if (Request::submittedSome(
                        $data['name'] . '_min',
                        $data['name'] . '_max'
                    )) {
                        $this->selected_criteria[$name]          = $data;
                        $this->selected_criteria[$name]['value'] = [
                            Request::get($data['name'] . '_min'),
                            Request::get($data['name'] . '_max')
                        ];
                    }
                } elseif ($data['type'] === 'bool') {
                    if (Request::submitted('options_' . $data['name'])) {
                        $this->selected_criteria[$name]          = $data;
                        $this->selected_criteria[$name]['value'] = Request::get($data['name']);
                    }
                } else {
                    if (Request::submitted($data['name'])) {
                        $this->selected_criteria[$name] = $data;
                        $this->selected_criteria[$name]['value'] = Request::get($data['name']);
                    }
                }
            }
        }

        $_SESSION['room_search_criteria']['room_search'] =
            $this->selected_criteria;
    }

    protected function restoreSearchFromSession()
    {
        if (!empty($_SESSION['room_search_criteria']['room_search']) && is_array($_SESSION['room_search_criteria']['room_search'])) {
            $this->selected_criteria =
                $_SESSION['room_search_criteria']['room_search'];
        } else {
            $this->selected_criteria = [];
        }
    }

    protected function search()
    {
        //The properties array is a "simplified" version of the
        //$selected_criteria array, stripped from all special search criteria,
        //except the "seats" search criteria.

        $properties = [];
        if ($this->selected_criteria) {
            foreach ($this->selected_criteria as $name => $criteria) {

                //Do not add the special properties
                //into the $properties array:
                if (preg_match('/special__/', $name) && ($name != 'special__seats')) {
                    continue;
                }
                if ($name == 'room_type' && empty($criteria['value'])) {
                    continue;
                }
                if ($name == 'room_category_id' && empty($criteria['value'])) {
                    continue;
                }
                if ($name == 'special__seats') {
                    if ($criteria['value'][0] || $criteria['value'][1]) {
                        $properties['seats'] = $criteria['value'];
                    }
                    $name = 'seats';
                } else {
                    $properties[$name] = $criteria['value'];
                }

                if (
                    isset($properties[$name][0], $properties[$name][1])
                    && $properties[$name][0] && $properties[$name][1]
                    && $properties[$name][0] > $properties[$name][1]
                    && $name !== 'room_category_id'
                ) {
                    //A range is selected, but the range start is bigger
                    //then the range end. That's an error!

                    //Resolve the property name for a "beautiful" property name:
                    $property = ResourcePropertyDefinition::findOneBySql(
                        'name = :name',
                        ['name' => $name]
                    );
                    $property_name = $name;
                    if ($property) {
                        $property_name = $property->display_name;
                    }

                    PageLayout::postError(
                        sprintf(
                            _('Für die Eigenschaft %1$s wurde ein ungültiger Bereich angegeben (von %2$s bis %3$s)!'),
                            htmlReady($property_name),
                            htmlReady($properties[$name][0]),
                            htmlReady($properties[$name][1])
                        )
                    );
                    return;
                }
            }
        }

        $building_or_location_id = explode(
            '_',
            $this->selected_criteria['special__building_location']['value']
        );

        $this->location_id = null;
        $this->building_id = null;

        if ($building_or_location_id[0] == 'building') {
            $this->building_id = $building_or_location_id[1];
        } elseif ($building_or_location_id[0] == 'location') {
            $this->location_id = $building_or_location_id[1];
        } elseif($building_or_location_id[0] == 'resourcelabel') {
            $resourcelabel = ResourceLabel::find($building_or_location_id[1]);
            if ($resourcelabel) {
                $sub_buildings = [];
                foreach($resourcelabel->findChildrenByClassName('Building') as $sub_building) {
                    $sub_buildings[] = $sub_building->id;
                }
                $this->building_id = $sub_buildings;
            }
        } elseif($building_or_location_id[0] == 'room') {
            $this->rooms = [Room::find($building_or_location_id[1])];
            return;
        }

        //The time intervals have to be calculated by the selected time range
        //and the selected day of week.
        //The selected semester is represented by the selected time range
        //since its begin and end date are set on the client side in
        //the special__available_range property when a semester is selected.
        $time_intervals = [];
        if (!empty($this->selected_criteria['special__time_range'])) {
            $time_range_criteria = $this->selected_criteria['special__time_range'];

            //Get and check day of week:
            if ($time_range_criteria['day_of_week']['value']) {
                $selected_dow = $time_range_criteria['day_of_week']['value'];
                if (($selected_dow >= 1) && ($selected_dow <= 7)) {

                    //Get and check the time range:
                    if (($time_range_criteria['range']['begin'] instanceof DateTime)
                        && ($time_range_criteria['range']['end'] instanceof DateTime)) {
                        //Start from the begin date and make time intervals
                        //for the specified time on the specified day of week.
                        $begin = clone $time_range_criteria['range']['begin'];
                        $begin_dow = $begin->format('N');
                        if ($begin_dow < $selected_dow) {
                            $diff = $selected_dow - $begin_dow;
                            $begin = $begin->add(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        } elseif ($begin_dow > $selected_dow) {
                            $diff = $begin_dow - $selected_dow;
                            $begin = $begin->sub(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        }
                        $end = clone $time_range_criteria['range']['end'];
                        $current_begin = clone $begin;
                        do {
                            $current_end = clone $current_begin;
                            $current_end->setTime(
                                intval($end->format('H')),
                                intval($end->format('i')),
                                intval($end->format('s'))
                            );
                            $time_intervals[] = [
                                'begin' => clone $current_begin,
                                'end' => clone $current_end
                            ];
                            $current_begin = $current_begin->add(
                                new DateInterval('P1W')
                            );
                        } while ($current_begin < $end);
                    } else {
                        //Get the next occurrence of the specified day of week.
                        $begin = new DateTime();
                        $begin_dow = $begin->format('N');
                        if ($begin_dow < $selected_dow) {
                            $diff = $selected_dow - $begin_dow;
                            $begin = $begin->add(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        } elseif ($begin_dow > $selected_dow) {
                            $diff = $begin_dow - $selected_dow;
                            $begin = $begin->sub(
                                new DateInterval(
                                    'P' . $diff . 'D'
                                )
                            );
                        }
                        $begin->setTime(0,0);
                        $end = clone $begin;
                        $end = $end->add(
                            new DateInterval('P1D')
                        )->sub(
                            new DateInterval('PT1S')
                        );

                        $time_intervals[] = [
                            'begin' => $begin,
                            'end' => $end
                        ];
                    }
                }
            } elseif ($time_range_criteria['range']) {
                //A time range without a day of week is specified.
                $time_intervals[] = $time_range_criteria['range'];
            }
        }

        try {
            $this->rooms = RoomManager::findRooms(
                $this->selected_criteria['special__room_name']['value'],
                $this->location_id,
                $this->building_id,
                $properties,
                $time_intervals,
                'name ASC, mkdate ASC',
                false
            );
        } catch (\InvalidArgumentException $e) {
            PageLayout::postError($e->getMessage());
        }
    }

    public function resetSearch()
    {
        $this->selected_criteria = [];
        $_SESSION['room_search_criteria']['room_search'] = [];
    }

    public function __construct($action_link = '')
    {
        parent::__construct();

        $this->template = 'sidebar/room-search-widget';

        if ($action_link) {
            $this->action_link = $action_link;
        }

        $this->setupSearchParameters();
        if ($this->searchRequested()) {
            $this->handleSearchRequest();
        } elseif ($this->searchResetRequested()) {
            $this->resetSearch();
        } else {
            $this->restoreSearchFromSession();
        }

        if ($this->selected_criteria) {
            $this->search();
        }
    }

    public function searchRequested()
    {
        return Request::submitted('room_search');
    }

    public function searchResetRequested()
    {
        return Request::submitted('room_search_reset');
    }

    public function getResults()
    {
        return $this->rooms;
    }

    public function setActionLink($action_link = '')
    {
        if (!$action_link) {
            return;
        }

        $this->action_link = $action_link;
    }

    public function getActionLink()
    {
        return $this->action_link;
    }

    public function getSelectedCriteria()
    {
        return $this->selected_criteria;
    }

    public function render($variables = [])
    {
        $variables = array_merge($variables, [
            'title'             => _('Suchkriterien für Räume'),
            'criteria'          => $this->criteria,
            'selected_criteria' => $this->selected_criteria,
            'action_link'       => $this->action_link,
            'semesters'         => $this->semesters
        ]);

        return $GLOBALS['template_factory']->render(
            $this->template,
            $variables,
            'widgets/widget-layout'
        );
    }
}
