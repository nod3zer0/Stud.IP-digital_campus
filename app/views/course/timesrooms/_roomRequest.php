<section class="contentbox">
    <header>
        <h1>
            <?= _('Raumanfrage für die gesamte Veranstaltung') ?>
        </h1>

        <nav>
            <?= tooltipIcon(
                _('Hier können Sie für die gesamte Veranstaltung, also für alle regelmäßigen und unregelmäßigen Termine, '
                    . 'eine Raumanfrage erstellen.')
            ) ?>
            <a class="link-add" href="<?= $controller->url_for('course/room_requests/new_request',
                [
                    'cid'                 => $course->id,
                    'range_str'           => 'course',
                    'origin'              => 'course_timesrooms',
                    'create_room_request' => 1
                ]
            ) ?>"
               data-dialog="size=big"
               title="<?= _('Neue Raumanfrage für die Veranstaltung erstellen') ?>">
                <?= _('Neue Raumanfrage') ?>
            </a>
        </nav>
    </header>

    <?php
    echo $flash['message'];
    ?>

    <? if (count($room_requests)) : ?>
    <section>
        <table class="default">
            <colgroup>
                <col style="width: 40%">
                <col style="width: 20%">
                <col>
                <col style="width: 50px">
            </colgroup>
            <thead>
            <tr>
                <th><?= _('Art der Anfrage') ?></th>
                <th><?= _('Anfragender') ?></th>
                <th><?= _('Bearbeitungsstatus') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <? foreach ($room_requests as $rr): ?>
                <tr>
                    <td>
                        <?= htmlReady($rr->getTypeString(), 1, 1) ?>
                    </td>
                    <td>
                        <?= htmlReady($rr->user ? $rr->user->getFullName() : '') ?>
                    </td>
                    <td>
                        <?= htmlReady($rr->getStatusText()) ?>
                    </td>
                    <td class="actions">
                        <a class="load-in-new-row"
                           href="<?= $controller->link_for('course/room_requests/info/' . $rr->id) ?>">
                            <?= Icon::create(
                                'info',
                                Icon::ROLE_CLICKABLE,
                                [
                                    'title' => _('Weitere Informationen einblenden')
                                ]
                            ) ?>
                        </a>
                        <? $params = [] ?>
                        <? $dialog = []; ?>
                        <? if (Request::isXhr()) : ?>
                            <? $params['asDialog'] = true; ?>
                            <? $dialog['data-dialog'] = 'size=big' ?>
                        <? endif ?>

                        <? $actionMenu = ActionMenu::get()->setContext($rr->getTypeString()) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/room_requests/request_summary/' . $rr->id, ['clear_cache' => 1]),
                            _('Diese Anfrage bearbeiten'),
                            Icon::create(
                                'edit',
                                Icon::ROLE_CLICKABLE,
                                [
                                    'title' => _('Diese Anfrage bearbeiten')
                                ]
                            ),
                            $dialog
                        ) ?>

                        <?php
                        if ($rr->room && !$user_has_permissions) {
                            $user_has_permissions = $rr->room->userHasPermission($current_user, 'admin');
                        }
                        ?>

                        <? if ($user_has_permissions && (int)$rr->closed === 0): ?>
                            <? $actionMenu->addLink(
                                URLHelper::getURL(
                                    'dispatch.php/resources/room_request/resolve/' . $rr->id,
                                    [
                                        'reload-on-close' => 1,
                                        'single-request'  => 1
                                    ]
                                ),
                                _('Diese Anfrage selbst auflösen'),
                                Icon::create(
                                    'admin',
                                    Icon::ROLE_CLICKABLE,
                                    [
                                        'title' => _('Diese Anfrage selbst auflösen')
                                    ]
                                ),
                                ['data-dialog' => '1']
                            ) ?>
                        <? endif ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/room_requests/delete/' . $rr->id),
                            _('Diese Anfrage löschen'),
                            Icon::create(
                                'trash',
                                Icon::ROLE_CLICKABLE,
                                [
                                    'title' => _('Diese Anfrage löschen')
                                ]
                            )
                        ) ?>
                        <?= $actionMenu->render() ?>
                    </td>
                </tr>
            <? endforeach ?>
            <? if ($request_id == $rr->id) : ?>
                <tr>
                    <td colspan="4">
                        <?= $this->render_partial('course/room_requests/_request.php', ['request' => $rr]); ?>
                    </td>
                </tr>
            <? endif ?>
            </tbody>
        </table>
    </section>
    <? else : ?>
        <?= MessageBox::info(_('Zu dieser Veranstaltung sind noch keine Raumanfragen vorhanden.')) ?>
    <? endif ?>

    <? if (Request::isXhr()) : ?>
        <div data-dialog-button>
            <?= \Studip\LinkButton::createEdit(
                _('Neue Raumanfrage erstellen'),
                $controller->url_for('course/room_requests/new/' . $course_id, $url_params),
                ['data-dialog' => 'size=big']
            ) ?>
        </div>
<? endif ?>
</section>
