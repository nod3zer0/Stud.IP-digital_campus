<section class="contentbox timesrooms">
    <header>
        <h1>
            <?= _('Regelmäßige Termine') ?>
        </h1>
    <? if(!$locked) : ?>
        <nav>
            <a class="link-add"
               href="<?= $controller->link_for('course/timesrooms/createCycle', $linkAttributes) ?>"
               data-dialog="size=600"
               title="<?= _('Regelmäßigen Termin hinzufügen') ?>">
                <?= _('Regelmäßigen Termin hinzufügen') ?>
            </a>
        </nav>
    <? endif ?>
    </header>

<? if (!empty($cycle_dates)) : ?>
    <? foreach ($cycle_dates as $metadate_id => $cycle) : ?>

        <form class="collapsable" action="<?= $controller->url_for('course/timesrooms/stack/' . $metadate_id, $linkAttributes) ?>"
              method="post" <?= Request::isXhr() ? 'data-dialog="size=big"' : ''?>>
            <?= CSRFProtection::tokenTag() ?>

            <article id="<?= $metadate_id ?>" class="<?= ContentBoxHelper::classes($metadate_id) ?>">
                <header class="<?= $course->getCycleColorClass($metadate_id) ?>">
                    <h1>
                    <? if ($info = $course->getBookedRoomsTooltip($metadate_id)) : ?>
                        <?= tooltipHtmlIcon($info) ?>
                    <? elseif ($course->getCycleColorClass($metadate_id) === 'red'): ?>
                        <?= tooltipIcon(_('Keine Raumbuchungen vorhanden')) ?>
                    <? else: ?>
                        <?= tooltipIcon(_('Keine offenen Raumbuchungen')) ?>
                    <? endif ?>
                        <a href="<?= ContentBoxHelper::href($metadate_id) ?>">
                            <?= htmlReady($cycle['cycle']->toString('long')) ?>
                        </a>
                    </h1>
                    <section>
                        <span>
                            <? if (isset($cycle_room_names[$cycle['cycle']->id])): ?>
                                <strong><?= _('Raum') ?>:</strong>
                                <?= htmlReady($cycle_room_names[$cycle['cycle']->id])?>
                            <? elseif (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
                                <? if (count($cycle['cycle']->room_requests) > 0): ?>
                                    <? $room_request = $cycle['cycle']->room_requests[0] ?>
                                    <? if ($room_request->closed <= '1'): ?>
                                        <strong>
                                            <?= sprintf(
                                                _('Raum %s angefragt'),
                                                htmlReady($cycle['cycle']->room_requests[0]->room->name)
                                            ) ?>
                                        </strong>
                                    <? else: ?>
                                        <strong><?= _('Bearbeitete Raumanfrage vorhanden!')?></strong>
                                    <? endif ?>
                                <? else: ?>
                                    <strong><?= _('Raumanfragen') ?></strong>:
                                    <?= _('keine') ?>
                                <? endif ?>
                            <? endif ?>
                        </span>
                    <? if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
                        <? $metadate = SeminarCycleDate::find($metadate_id) ?>
                        <? $open_requests = $metadate->countOpenRequestsForDates() ?>
                        <span>
                            <strong><?= _('Einzel-Raumanfrage') ?></strong>:
                            <?= htmlReady(
                                $open_requests > 0
                                ? $open_requests
                                : _('keine offenen Anfragen')
                            ) ?>
                        </span>
                    <? endif ?>
                    </section>
                    <? if (!$locked) : ?>
                    <nav>
                        <? $actionMenu = ActionMenu::get()->setContext($cycle['cycle']->toString('long')) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('course/timesrooms/createCycle/' . $metadate_id, $linkAttributes),
                            _('Diesen Zeitraum bearbeiten'),
                            Icon::create(
                                'edit',
                                Icon::ROLE_CLICKABLE,
                                ['title' => _('Diesen Zeitraum bearbeiten'), 'style' => 'vertical-align: middle;']
                            ),
                            ['data-dialog' => 'size=600']
                        ) ?>
                        <? if (Config::get()->RESOURCES_ALLOW_ROOM_REQUESTS) : ?>
                            <? $actionMenu->addLink(
                                $controller->url_for(
                                    'course/room_requests/new_request/',
                                    [
                                        'cid'       => $course->id,
                                        'range_str' => 'cycle',
                                        'range_id'  => $metadate_id
                                    ]
                                ),
                                _('Neue Raumanfrage'),
                                Icon::create(
                                    'room-request',
                                    Icon::ROLE_CLICKABLE,
                                    [
                                        'title' => _('Neue Raumanfrage'),
                                        'style' => 'vertical-align: middle;'
                                    ]
                                ),
                                ['data-dialog' => 'size=big']
                            ) ?>
                        <? endif ?>
                        <? $actionMenu->addButton(
                            'delete_cycle',
                            _('Diesen Zeitraum löschen'),
                            Icon::create('trash', Icon::ROLE_CLICKABLE, ['title' => _('Diesen Zeitraum löschen')]),
                            [
                                'formaction'   => $controller->url_for('course/timesrooms/deleteCycle/' . $metadate_id, $linkAttributes),
                                'data-confirm' => _('Soll dieser Zeitraum wirklich gelöscht werden?')
                                                  . "\n"
                                                  . _('Ein Löschen des Zeitraums führt auch zum Verlust einer vorhandenen Raumbuchung.')
                            ]) ?>
                        <?= $actionMenu->render() ?>
                    </nav>
                    <? endif ?>
                </header>
                <section>
                    <table class="default">
                        <colgroup>
                            <? if (!$locked) : ?>
                                <col style="width: 30px">
                            <? endif ?>
                            <col style="width: 30%">
                            <col>
                            <col>
                            <col style="width: 20%">
                            <col style="width: 50px">
                        </colgroup>
                    <? foreach ($cycle['dates'] as $semester_id => $termine) : ?>
                        <thead>
                            <tr>
                                <th colspan="<?= !$locked ? 6 : 5?>">
                                    <label>
                                        <? if(!$locked) : ?>
                                            <input type="checkbox" class="date-proxy_<?= $metadate_id ?>"
                                                   data-proxyfor="#<?= $metadate_id ?>-<?= $semester_id ?> .ids-regular">
                                        <? endif ?>
                                        <?= htmlReady(Semester::find($semester_id)->name) ?>
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="<?= $metadate_id ?>-<?= $semester_id ?>">
                        <? foreach ($termine as $termin) : ?>
                            <?= $this->render_partial(
                                'course/timesrooms/_cycleRow.php',
                                ['termin' => $termin, 'class_ids' => 'ids-regular']
                            ) ?>
                        <? endforeach ?>
                        </tbody>
                    <? endforeach ?>
                        <? if(!$locked) : ?>
                            <tfoot>
                                <tr>
                                    <td colspan="2">
                                        <label>
                                            <input type="checkbox"
                                                    data-proxyfor=".date-proxy_<?= $metadate_id ?>"
                                                    data-activates=".actionForAllRegular_<?= $metadate_id ?>">
                                            <?= _('Alle auswählen') ?>
                                        </label>
                                    </td>
                                    <td colspan="4" class="actions">
                                        <select name="method" class="datesBulkActions actionForAllRegular_<?= $metadate_id ?>">
                                            <?= $this->render_partial('course/timesrooms/_stack_actions.php') ?>
                                        </select>
                                        <?= Studip\Button::create(
                                            _('Ausführen'),
                                            'run',
                                            [
                                                'class' => 'actionForAllRegular_' . $metadate_id,
                                                'data-dialog' => 'size=big'
                                            ]
                                        ) ?>
                                    </td>
                                </tr>
                            </tfoot>
                        <? endif ?>
                    </table>

                </section>
            </article>
        </form>
    <? endforeach ?>

<? else: ?>
    <section>
        <p class="text-center">
            <strong><?= _('Keine regelmäßigen Termine vorhanden') ?></strong>
        </p>
    </section>
<? endif ?>
</section>
