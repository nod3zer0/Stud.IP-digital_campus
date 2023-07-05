<? if ($user): ?>
    <div>
        <dl>
            <dt><?= _('Globale Berechtigung') ?></dt>
            <dd>
                <? if ($global_permission): ?>
                    <?= htmlReady($global_permission->perms) ?>
                <? else: ?>
                    <?= _('keine') ?>
                <? endif ?>
            </dd>
            <dt><?= _('Aktivität') ?></dt>
            <dd title="<?= htmlReady($last_activity_date) ?>">
                <? if ($last_activity): ?>
                    <? if ($last_activity->y): ?>
                        <?= sprintf(
                            ngettext(
                                'Inaktiv seit mehr als einem Jahr',
                                'Inaktiv seit mehr als %d Jahren',
                                $last_activity->y
                            ),
                            $last_activity->y
                        ) ?>
                    <? elseif ($last_activity->m): ?>
                        <?= sprintf(
                            ngettext(
                                'Inaktiv seit mehr als einem Monat',
                                'Inaktiv seit mehr als %d Monaten',
                                $last_activity->m
                            ),
                            $last_activity->m
                        ) ?>
                    <? elseif ($last_activity->d): ?>
                        <? if ($last_activity->d == 1): ?>
                            <?= _('Gestern zuletzt aktiv.') ?>
                        <? elseif ($last_activity->d == 2): ?>
                            <?= _('Vorgestern zuletzt aktiv.') ?>
                        <? else: ?>
                            <?= sprintf(
                                _('Inaktiv seit %d Tagen'),
                                $last_activity->d
                            ) ?>
                        <? endif ?>
                    <? else: ?>
                        <?= _('Innerhalb der letzten 24 Stunden zuletzt aktiv gewesen.') ?>
                    <? endif ?>
                <? elseif ($last_activity === null): ?>
                    <?= _('keine') ?>
                <? else: ?>
                    <?= _('Fehler') ?>
                <? endif ?>
            </dd>
        </dl>
        <? if ($last_activity !== null): ?>
            <?= \Studip\LinkButton::create(
                _('Liste mit Buchungen anzeigen'),
                URLHelper::getLink(
                    'dispatch.php/resources/admin/booking_log/'
                  . $user->id
                ),
                [
                    'data-dialog' => '1'
                ]
            ) ?>
        <? endif ?>
    </div>
    <? if ($temporary_permissions): ?>
        <form class="default" id="permissions_temporary" method="post" action="<?= $controller->link_for('resources/admin/delete_permissions') ?>">
            <?= CSRFProtection::tokenTag() ?>
            <input type="hidden" name="permission_type" value="temporary">
            <input type="hidden" name="user_id" value="<?= htmlReady($user->id) ?>">
            <table class="default resources_permissions-table sortable-table"
                   data-sortlist="[[0, 0]]">
                <caption>
                    <?= _('Temporäre Berechtigungen') ?>
                </caption>
                <colspan>
                    <col class="checkbox">
                    <col>
                    <col>
                    <col>
                    <col>
                </colspan>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" data-proxyfor="#permissions_temporary input[name='resource_ids[]']"
                                   data-activates="#permissions_temporary tfoot .button"
                                   title="<?= htmlReady(sprintf(
                                       _('Alle Berechtigungen von %s auswählen'), $user->getFullName()
                                   )) ?>">
                        </th>
                        <th data-sort="text"><?= _('Name der Ressource') ?></th>
                        <th data-sort="1"><?= _('Berechtigung') ?></th>
                        <th><?= _('Gültigkeit') ?></th>
                        <th class="actions"><?= _('Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($temporary_permissions as $permission): ?>
                        <?
                        $resource = $permission->resource->getDerivedClassInstance();
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="resource_ids[]"
                                       value="<?= htmlReady($permission->resource_id) ?>"
                                       title="<?= htmlReady(sprintf(_('Berechtigung für %s auswählen'), $resource)) ?>">
                            </td>
                            <td>
                                <?= htmlReady($resource) ?>
                            </td>
                            <td>
                                <?= htmlReady($permission->perms) ?>
                            </td>
                            <td>
                                <?= date('d.m.Y H:i', $permission->begin) ?>
                                -
                                <?= date('d.m.Y H:i', $permission->end) ?>
                            </td>
                            <td class="actions">
                                <a href="<?= $permission->resource->getActionLink(
                                    'temporary_permissions',
                                    [
                                        'user_id' => $permission->user_id
                                    ]
                                ) ?>" data-dialog>
                                    <?= Icon::create('edit')->asImg(
                                        '20px',
                                        [
                                            'class' => 'text-bottom',
                                            'title' => _('Berechtigung bearbeiten')
                                        ]
                                    ) ?>
                                </a>
                                <a href="<?= URLHelper::getLink(
                                    'dispatch.php/resources/admin/booking_log/'
                                    . $user->id
                                    . '/'
                                    . $permission->resource_id
                                ) ?>" data-dialog>
                                    <?= Icon::create('log')->asImg(
                                        [
                                            'class' => 'text-bottom',
                                            'title' => 'Liste mit Buchungen anzeigen'
                                        ]
                                    ) ?>
                                </a>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <?= \Studip\Button::create(_('Löschen'), 'delete',
                                [
                                    'data-confirm' => sprintf(
                                        _('Sollen die gewählten Berechtigungen von %s wirklich gelöscht werden?'),
                                        $user->getFullName()
                                    )
                                ]
                            ) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    <? endif ?>
    <? if ($permissions): ?>
        <form class="default" id="permissions_permanent" method="post"
              action="<?= $controller->link_for('resources/admin/delete_permissions') ?>">
            <?= CSRFProtection::tokenTag() ?>
            <input type="hidden" name="permission_type" value="permanent">
            <input type="hidden" name="user_id" value="<?= htmlReady($user->id) ?>">
            <table class="default resources_permissions-table sortable-table"
                   data-sortlist="[[0, 0]]">
                <caption>
                    <?= _('Dauerhafte Berechtigungen') ?>
                </caption>
                <colspan>
                    <col class="checkbox">
                    <col>
                    <col>
                    <col>
                </colspan>
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" data-proxyfor="#permissions_permanent input[name='resource_ids[]']"
                                   data-activates="#permissions_permanent tfoot .button"
                                   title="<?= htmlReady(sprintf(
                                       _('Alle Berechtigungen von %s auswählen'), $user->getFullName()
                                   )) ?>">
                        </th>
                        <th data-sort="text"><?= _('Name der Ressource') ?></th>
                        <th data-sort="text"><?= _('Berechtigung') ?></th>
                        <th class="actions"><?= _('Aktionen') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($permissions as $permission): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="resource_ids[]"
                                       value="<?= htmlReady($permission->resource_id) ?>"
                                       title="<?= htmlReady(sprintf(_('Berechtigung für %s auswählen'), $resource)) ?>">
                            </td>
                            <td>
                                <?= htmlReady($permission->resource->getDerivedClassInstance()) ?>
                            </td>
                            <td>
                                <?= htmlReady($permission->perms) ?>
                            </td>
                            <td class="actions">
                                <a href="<?= $permission->resource->getActionLink(
                                    'permissions',
                                    [
                                        'user_id' => $permission->user_id
                                    ]
                                ) ?>" data-dialog>
                                    <?= Icon::create('edit')->asImg(
                                        [
                                            'class' => 'text-bottom',
                                            'title' => _('Berechtigung bearbeiten')
                                        ]
                                    ) ?>
                                </a>
                                <a href="<?= URLHelper::getLink(
                                    'dispatch.php/resources/admin/booking_log/'
                                    . $user->id
                                    . '/'
                                    . $permission->resource_id
                                ) ?>" data-dialog>
                                    <?= Icon::create('log')->asImg(
                                        [
                                            'class' => 'text-bottom',
                                            'title' => _('Liste mit Buchungen anzeigen')
                                        ]
                                    ) ?>
                                </a>
                            </td>
                        </tr>
                    <? endforeach ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <?= Studip\Button::create(_('Löschen'), 'delete',
                                [
                                    'data-confirm' => sprintf(
                                        _('Sollen die gewählten Berechtigungen von %s wirklich gelöscht werden?'),
                                        $user->getFullName()
                                    )
                                ]
                            ) ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </form>
    <? endif ?>
<? elseif ($users) : ?>
    <form class="default" id="permissions_all_from_user" method="post"
          action="<?= $controller->link_for('resources/admin/delete_permissions') ?>">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="permission_type" value="all_from_users">
        <table class="default sortable-table" data-sortlist="[[0, 0]]">
            <caption>
                <?= _('Personen mit Berechtigungen an der Raumverwaltung') ?>
            </caption>
            <colspan>
                <col class="checkbox">
                <col>
            </colspan>
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" data-proxyfor="#permissions_all_from_user input[name='user_ids[]']"
                               data-activates="#permissions_all_from_user tfoot .button"
                               title="<?= _('Alle Berechtigungen auswählen') ?>">
                    </th>
                    <th data-sort="text"><?= _('Nachname, Vorname') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($users as $user) : ?>
                    <tr>
                        <td>
                            <input type="checkbox" name="user_ids[]"
                                   value="<?= htmlReady($user->id) ?>"
                                   title="<?= htmlReady(sprintf(_('%s auswählen'), $user->getFullName())) ?>">
                        </td>
                        <td>
                            <a href="<?= $controller->link_for(
                                'resources/admin/user_permissions',
                                ['user_id' => $user->id]
                            ) ?>">
                                <?= htmlReady($user->getFullName('full_rev')) ?>
                                <?= Icon::create('link-intern')->asImg(
                                    [
                                        'class' => 'text-bottom'
                                    ]
                                ) ?>
                            </a>
                        </td>
                    </tr>
                <? endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?= Studip\Button::create(_('Löschen'),'delete',
                            [
                                'data-confirm' => _('Sollen alle Berechtigungen der ausgewählten Personen wirklich gelöscht werden?')
                            ]
                        ) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </form>
<? endif ?>
