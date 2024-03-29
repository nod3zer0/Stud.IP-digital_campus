<? if (!$print_schedules): ?>

    <? if ($clipboard_selected): ?>
        <form class="default" method="post"
              action="<?= $controller->link_for('resources/print/clipboard_rooms') ?>">
            <?= CSRFProtection::tokenTag() ?>

            <input type="hidden" name="clipboard_id" value="<?= htmlReady($selected_clipboard_id) ?>">
            <input type="hidden" name="schedule_type" value="<?= htmlReady($schedule_type) ?>">
            <input type="hidden" name="date" value="<?= htmlReady($selected_date_string) ?>">
            <? foreach ($selected_booking_types as $type) : ?>
                <input type="hidden" name="bookingtypes[]" value="<?= $type ?>">
            <? endforeach ?>
            <fieldset>
                <? if (!$available_rooms): ?>
                    <?= MessageBox::info(
                        sprintf(
                            _('In der Raumgruppe %s sind keine Räume vorhanden!'),
                            htmlReady($selected_clipboard->name)
                        )
                    ) ?>
                <? else: ?>
                    <legend>
                        <?= htmlReady(
                            sprintf(
                                _('Bitte Räume aus der Raumgruppe %s auswählen'),
                                htmlReady($selected_clipboard->name)
                            )
                        ) ?>
                    </legend>
                    <ul>
                        <? foreach ($available_rooms as $room): ?>
                            <li>
                                <label>
                                    <input type="checkbox" value="<?= htmlReady($room->id) ?>"
                                           checked="checked"
                                           name="selected_room_ids[]">
                                    <?= htmlReady($room->getFullName()) ?>
                                </label>
                            </li>
                        <? endforeach ?>
                    </ul>
                <? endif ?>
            </fieldset>
            <div data-dialog-button>
                <? if ($available_rooms): ?>
                    <?= \Studip\Button::create(
                        _('Drucken'),
                        'print'
                    ) ?>
                <? endif ?>
                <?= \Studip\Button::create(
                    _('Raumgruppe neu wählen'),
                    'null'
                ) ?>
            </div>
        </form>
    <? else: ?>
        <? if(count($available_clipboards)) : ?>
            <form class="default" method="post"
                  action="<?= $controller->link_for('resources/print/clipboard_rooms') ?>">
                <?= CSRFProtection::tokenTag() ?>

                <fieldset>
                    <label>
                        <?= _('Individuelle Raumgruppe') ?>:
                        <select name="clipboard_id">
                            <? foreach ($available_clipboards as $clipboard): ?>
                                <option value="<?= htmlReady($clipboard->id) ?>">
                                    <?= htmlReady($clipboard->name) ?>
                                </option>
                            <? endforeach ?>
                        </select>
                    </label>
                    <label>
                        <?= _('Art des Belegungsplanes') ?>:
                        <select name="schedule_type">
                            <option value="w"
                                <?= $selected_schedule == 'w'
                                    ? 'selected="selected"'
                                    : '' ?>>
                                <?= _('Wochenplan') ?>
                            </option>
                            <option value="w+we"
                                <?= $selected_schedule == 'w+we'
                                    ? 'selected="selected"'
                                    : '' ?>>
                                <?= _('Wochenplan inklusive Wochenende') ?>
                            </option>
                            <option value="d"
                                <?= $selected_schedule == 'd'
                                    ? 'selected="selected"'
                                    : '' ?>>
                                <?= _('Tagesplan') ?>
                            </option>
                        </select>
                    </label>
                    <label>
                        <?= _('Datum')?>:
                        <input type="text" name="date"
                               value="<?= date('d.m.Y') ?>"
                               class="has-date-picker">
                    </label>
                    <label>
                        <?= _('Zu exportierende Belegungstypen') ?>
                        <select name="bookingtypes[]" multiple class="nested-select">
                            <? foreach ($booking_types as $index => $name) : ?>
                                <option value="<?= $index ?>"
                                    <?= in_array($index, $selected_booking_types) ? ' selected' : '' ?>>
                                    <?= htmlReady($name) ?></option>
                            <? endforeach ?>
                        </select>
                    </label>
                </fieldset>
                <div data-dialog-button>
                    <?= \Studip\Button::create(
                        _('Weiter zur Raumauswahl'),
                        'select_clipboard'
                    ) ?>
                </div>
            </form>
        <? else :?>
            <?= MessageBox::info(
                _('Sie müssen zunächst Raumgruppen erstellen'),
                [
                    sprintf(
                        _('Klicken Sie %shier%s, um eine Raumgruppe anzulegen.'),
                        '<a href="' . URLHelper::getLink('dispatch.php/room_management/overview/rooms') . '">',
                        '</a>')
                ]
            )?>
        <? endif ?>
    <? endif ?>
<? else: ?>
    <? if (Request::get("allday")) {
        $min_time = '00:00:00';
        $max_time = '24:00:00';
    } else {
        $min_time = Config::get()->RESOURCES_BOOKING_PLAN_START_HOUR . ':00';
        $max_time = Config::get()->RESOURCES_BOOKING_PLAN_END_HOUR . ':00';
    } ?>
    <?
    PageLayout::setTitle('');
    ?>
    <? foreach ($selected_rooms as $room): ?>
        <section class="booking-plan-area">
            <h1>
                <?= sprintf(
                    _('%s: Belegungsplan'),
                    htmlReady($room->getFullName())
                ) ?>
            </h1>
            <?= \Studip\Fullcalendar::create(
                '',
                [
                    'editable' => true,
                    'selectable' => false,
                    'studip_urls' => [],
                    'minTime' => ($min_time),
                    'maxTime' => ($max_time),
                    'allDaySlot' => false,
                    'header' => [
                        'left' => 'dayGridMonth,timeGridWeek,timeGridDay',
                        'right' => 'prev,next'
                    ],
                    'defaultView' =>
                        in_array(Request::get("defaultView"), ['dayGridMonth','timeGridWeek','timeGridDay'])
                            ? Request::get("defaultView")
                            : 'timeGridWeek',
                    'defaultDate' => Request::get("defaultDate", $print_date),
                    'eventSources' => [
                        [
                            'url' => URLHelper::getURL(
                                'api.php/resources/resource/'
                                . $room->id . '/booking_plan'
                            ),
                            'method' => 'GET',
                            'extraParams' => [
                                'booking_types' => $selected_booking_types,
                                'display_requests' => 1
                            ]
                        ]
                    ]
                ],
                ['class' => 'resource-plan']
            ) ?>
            <? if ($additional_text != '') : ?>
                <div>
                    <br>
                    <?= formatReady($additional_text) ?>
                </div>
            <? endif ?>
        </section>
    <? endforeach ?>
<? endif ?>
