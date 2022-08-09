<? if ($requests): ?>
    <form class="default" method="post"
          action="<?= $controller->link_for('room_request/assign') ?>">
        <table class="default request-list tablesorter">
            <caption>
                <? if ($request_status === 'closed') : ?>
                    <?= sprintf(
                        ngettext(
                            'Anfragenliste (%d bearbeitete Anfrage)',
                            'Anfragenliste (%d bearbeitete Anfragen)',
                            $count_requests
                        ),
                        $count_requests
                    ) ?>
                <? elseif ($request_status === 'denied') : ?>
                    <?= sprintf(
                        ngettext(
                            'Anfragenliste (%d abgelehnte Anfrage)',
                            'Anfragenliste (%d abgelehnte Anfragen)',
                            $count_requests
                        ),
                        $count_requests
                    ) ?>
                <? else : ?>
                    <?= sprintf(
                        ngettext(
                            'Anfragenliste (%d Anfrage)',
                            'Anfragenliste (%d Anfragen)',
                            $count_requests
                        ),
                        $count_requests
                    ) ?>
                <? endif ?>
            </caption>
            <thead>
                <tr class="sortable">
                    <th <? if ($sort_var === 1) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 1 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=1&sort_order=%s', $sortorder)) ?>">
                         <?= Icon::create('radiobutton-checked')->asImg(
                             [
                                 'title' => _('Markierung')
                             ]
                        ) ?></a>
                    </th>
                    <th <? if ($sort_var == 2) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 2 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=2&sort_order=%s', $sortorder)) ?>">
                        <?= _('Nr.') ?></a>
                    </th>
                    <th <? if ($sort_var === 3) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 3 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=3&sort_order=%s', $sortorder)) ?>">
                        <?= _('Name') ?></a>
                    </th>
                    <th <? if ($sort_var === 4) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 4 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=4&sort_order=%s', $sortorder)) ?>">
                        <?= _('Lehrende Person(en)') ?></a>
                    </th>
                    <th <? if ($sort_var === 5) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 5 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=5&sort_order=%s', $sortorder)) ?>">
                        <?= _('Raum') ?></a>
                    </th>
                    <th <? if ($sort_var === 6) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 6 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=6&sort_order=%s', $sortorder)) ?>">
                        <?= _('Plätze') ?></a>
                    </th>
                    <th <? if ($sort_var === 7) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 7 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=7&sort_order=%s', $sortorder)) ?>">
                        <?= _('Anfragende Person') ?></a>
                    </th>
                    <th <? if ($sort_var === 8) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 8 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=8&sort_order=%s', $sortorder)) ?>">
                        <?= _('Art') ?></a>
                    </th>
                    <th <? if ($sort_var === 9) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 9 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=9&sort_order=%s', $sortorder)) ?>">
                        <?= _('Dringlichkeit') ?></a>
                    </th>
                    <th <? if ($sort_var === 10) printf('class="sort%s"', $sort_order) ?>>
                        <? $sortorder = $sort_var !== 10 ? 'desc' : ($sort_order === 'asc' ? 'desc' : 'asc') ?>
                        <a href="<?= URLHelper::getLink(sprintf('?sorting=10&sort_order=%s', $sortorder)) ?>">
                        <?= _('letzte Änderung') ?></a>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($requests as $request): ?>
                    <? if ($request->getTimeIntervals()) : ?>
                        <?= $this->render_partial(
                            'resources/_common/_request_tr',
                            ['request' => $request]
                        ) ?>
                    <? endif ?>
                <? endforeach ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="11">
                    <section style="float: right">
                        <?= $pagination->asLinks(function ($page) use ($controller) {
                            return $controller->url_for("resources/room_request/overview/{$page}");
                        }) ?>
                    </section>
                </td>
            </tr>
            </tfoot>
        </table>
    </form>
<? else: ?>
    <?= MessageBox::info(_('Es sind keine Anfragen vorhanden!')) ?>
<? endif ?>
