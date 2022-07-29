<? if (is_array($rooms) && count($rooms)): ?>
    <table class="default sortable-table">
        <colgroup>
            <col style="width: 20px">
            <col style="width: 30%">
            <col style="width: 30%">
        </colgroup>
        <thead>
            <tr>
                <th data-sort="text" colspan="2"><?= _('Name')?></th>
                <th><?= _('Beschreibung')?></th>
                <th data-sort="number" ><?= _('Sitzplätze')?></th>
                <th class="actions"><?= _('Aktion')?></th>
            </tr>
        </thead>
        <tbody>
            <? foreach ($rooms as $room): ?>
                <tr>
                    <td>
                        <?= Assets::img(
                            'anfasser_24.png',
                            [
                                'class'           => 'clipboard-draggable-item',
                                'data-id'         => $room->id,
                                'data-range_type' => 'Room',
                                'data-name'       => $room->name
                            ]
                        ) ?>
                    </td>
                    <td>
                        <? if ($room->bookingPlanVisibleForUser($current_user)): ?>
                            <a href="<?= $room->getActionLink('booking_plan', $booking_plan_action_params) ?>" data-dialog="size=big">
                                <?= htmlReady($room->name) ?>
                            </a>
                        <? else : ?>
                            <?= htmlReady($room->name) ?>
                        <? endif ?>
                    </td>
                    <td>
                        <? if ($room->description): ?>
                            <?= htmlReady($room->description) ?>
                        <? endif ?>
                    </td>
                    <td>
                        <? if ($room->seats): ?>
                            <?= htmlReady($room->seats) ?>
                        <? endif ?>
                    </td>
                    <td class="actions">
                        <?
                        $actions = ActionMenu::get();
                        $actions->addLink(
                            $room->getActionURL('show'),
                            _('Raumdetails anzeigen'),
                            Icon::create('info-circle'),
                            ['data-dialog' => '']
                        );
                        if ($room->userHasPermission($current_user, 'autor')) {
                            $actions->addLink(
                                $room->getActionURL('booking_plan', $booking_plan_action_params),
                                _('Wochenbelegung'),
                                Icon::create('timetable'),
                                ['target' => '_blank']
                            );
                            $actions->addLink(
                                $room->getActionURL('semester_plan'),
                                _('Semesterbelegung'),
                                Icon::create('timetable'),
                                ['target' => '_blank']
                            );
                        } else {
                            if ($room->booking_plan_is_public && Config::get()->RESOURCES_SHOW_PUBLIC_ROOM_PLANS) {
                                $actions->addLink(
                                    $room->getActionURL('booking_plan', $booking_plan_action_params),
                                    _('Belegungsplan'),
                                    Icon::create('timetable'),
                                    ['data-dialog' => 'size=big']
                                );
                                $actions->addLink(
                                    $room->getActionURL('semester_plan'),
                                    _('Semesterbelegung'),
                                    Icon::create('timetable'),
                                    ['data-dialog' => 'size=big']
                                );
                            }
                        }
                        if ($room->requestable && $room->userHasRequestRights($current_user)) {
                            $actions->addLink(
                                $room->getActionURL('request'),
                                _('Raum anfragen'),
                                Icon::create('room-request'),
                                ['data-dialog' => 'size=auto']
                            );
                        }
                        if ($room->building) {
                            $geo_coordinates_object = $room->building->getPropertyObject('geo_coordinates');
                            if ($geo_coordinates_object instanceof ResourceProperty) {
                                $actions->addLink(
                                    ResourceManager::getMapUrlForResourcePosition(
                                        $room->building->getPropertyObject('geo_coordinates')
                                    ),
                                    _('Zum Lageplan'),
                                    Icon::create('globe'),
                                    ['target' => '_blank']
                                );
                            }
                        }
                        if ($clipboard_widget_id) {
                            $actions->addLink(
                                '#',
                                _('Zur Raumgruppe hinzufügen'),
                                IcoN::create('add'),
                                [
                                    'class'             => 'clipboard-add-item-button',
                                    'data-range_type'   => 'Room',
                                    'data-range_id'     => $room->id,
                                    'data-clipboard_id' => $clipboard_widget_id
                                ]
                            );
                        }
                        echo $actions->render();
                        ?>
                    </td>
                </tr>
            <? endforeach ?>
        </tbody>
    </table>
<? else: ?>
    <? if ($form_submitted && !$has_errors): ?>
        <?= MessageBox::info(
            _('Es wurden keine Räume gefunden, die zu den angegebenen Suchkriterien passen!')
        ) ?>
    <? endif ?>
    <? if (!$form_submitted): ?>
        <?= MessageBox::info(
            _('Wählen Sie Suchkriterien oder ein Element im Ressourcenbaum, um Räume zu finden.')
        ) ?>
    <? endif ?>
<? endif ?>
