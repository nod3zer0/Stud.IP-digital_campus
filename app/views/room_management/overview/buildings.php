<? if ($buildings): ?>
    <form class="default building-list" method="post"
          action="<?= URLHelper::getLink('dispatch.php/room_management/overview/buildings') ?>">
        <table class="default building-list">
            <colgroup>
                <col class="checkbox">
                <col>
                <col>
                <col>
                <col>
            </colgroup>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" class="proxy"
                               data-proxyfor="input[name='building_ids[]']"
                               data-activates="form.building-list button.bulk-action"
                               aria-label="<?= _('Alle Gebäude auswählen') ?>">
                    </th>
                    <th><?= _('Name') ?></th>
                    <th><?= _('Nummer') ?></th>
                    <th><?= _('Adresse') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>

            <tbody>
                <? foreach ($buildings as $building): ?>
                    <?= $this->render_partial(
                        'resources/_common/_resource_tr.php',
                        [
                            'resource' => $building,
                            'booking_plan_on_link_name' => false,
                            'show_global_admin_actions' => $user_is_global_resource_admin,
                            'show_admin_actions' => $building->userHasPermission(
                                $user,
                                'admin'
                            ),
                            'show_tutor_actions' => $building->userHasPermission(
                                $user,
                                'tutor'
                            ),
                            'show_autor_actions' => $building->userHasPermission(
                                $user,
                                'autor'
                            ),
                            'show_user_actions' => $building->userHasPermission(
                                $user,
                                'user'
                            ),
                            'checkbox_data' => [
                                'name' => 'building_ids[]',
                                'checked' => in_array($building->id, $building_ids)
                            ],
                            'show_picture' => true,
                            'show_full_name' => false,
                            'additional_properties' => [
                                'number',
                                'address'
                            ],
                            'additional_actions' => [
                                '0020' => null,
                                '0030' => null,
                                '0040' => null,
                                '0050' => null,
                                '0070' => null,
                                '0080' => null,
                                '0090' => null
                            ]
                        ]
                    ) ?>
                <? endforeach ?>
            </tbody>
        </table>
        <footer>
            <?
            $button_attrs = [
                'class' => 'bulk-action',
                'data-activates-condition' => 'table.building-list :checkbox:checked'
            ];
            if (!$building_ids) {
                $button_attrs['disabled'] = 'disabled';
            }
            ?>
            <?= \Studip\Button::create(
                _('Raumgruppen für Gebäude erstellen'),
                'create_clipboards',
                array_merge($button_attrs, ['aria-title' => _('Raumgruppen für Gebäude erstellen')])
            ) ?>
            <?= \Studip\Button::create(
                _('Berechtigungen für alle Räume setzen'),
                'room_permissions',
                array_merge(
                    $button_attrs,
                    [
                        'data-dialog' => 'size=big',
                        'aria-title' => _('Berechtigungen für alle Räume setzen')
                    ]
                )
            ) ?>
        </footer>
    </form>
<? endif ?>
