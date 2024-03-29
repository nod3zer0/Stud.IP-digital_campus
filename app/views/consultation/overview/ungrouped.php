<? if (count($blocks) === 0): ?>

<?= MessageBox::info(_('Aktuell werden keine Termine angeboten.'))->hideClose() ?>

<? else: ?>

<table class="default">
    <colgroup>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col>
        <col style="width: 96px">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Tag') ?></th>
            <th><?= _('Zeit') ?></th>
            <th><?= _('Bei') ?></th>
            <th><?= _('Ort') ?></th>
            <th><?= _('Status') ?></th>
            <th><?= _('Person(en)') ?></th>
            <th><?= _('Grund') ?></th>
            <th class="actions"><?= _('Optionen') ?></th>
        </tr>
    </thead>
<? foreach ($blocks as $block): ?>
    <tbody id="block-<?= htmlReady($block->id) ?>">
    <? foreach ($block->slots as $slot):
        if (!$slot->block->show_participants && $slot->isOccupied() && !$slot->isOccupied($GLOBALS['user']->id)) continue;
    ?>
        <tr id="slot-<?= htmlReady($slot->id) ?>">
            <td>
                <?= strftime(_('%A, %x'), $slot->start_time) ?>
            </td>
            <td>
                <?= strftime('%H:%M', $slot->start_time) ?>
                -
                <?= strftime('%H:%M', $slot->end_time) ?>

                <?= $displayNote($slot->note, 29, 'below') ?>
            </td>
            <td>
            <? if (count($block->responsibilities) > 0): ?>
                <ul class="default">
                <? foreach ($block->responsibilities as $responsibility): ?>
                    <li>
                        <a href="<?= URLHelper::getLink($responsibility->getURL(), [], true) ?>">
                            <?= htmlReady($responsibility->getName()) ?>
                        </a>
                    </li>
                <? endforeach; ?>
                </ul>
            <? endif; ?>
            </td>
            <td>
                <?= formatLinks($block->room) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-occupation.php', compact('slot')) ?>
            </td>
            <td>
                <?= $this->render_partial('consultation/slot-bookings.php', compact('slot')) ?>
            </td>
            <td>
            <? if ($slot->isOccupied($GLOBALS['user']->id)): ?>
                <?= htmlReady($slot->bookings->findOneBy('user_id', $GLOBALS['user']->id)->reason) ?>
            <? endif; ?>
            </td>
            <td class="actions">
            <? if ($slot->isOccupied($GLOBALS['user']->id)): ?>
                <a href="<?= $controller->cancel($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('trash')->asImg(tooltip2(_('Termin absagen'))) ?>
                </a>
            <? elseif ($slot->userMayCreateBookingForSlot()): ?>
                <a href="<?= $controller->book($block, $slot) ?>" data-dialog="size=auto">
                    <?= Icon::create('add')->asImg(tooltip2(_('Termin reservieren'))) ?>
                </a>
            <? else: ?>
                <?= Icon::create('add', Icon::ROLE_INACTIVE)->asImg(tooltip2(_('Dieser Termin ist für Buchungen gesperrt.'))) ?>
            <? endif; ?>
            </td>
        </tr>
    <? endforeach; ?>
    </tbody>
<? endforeach; ?>
<? if ($count > $limit): ?>
    <tfoot>
        <tr>
            <td colspan="8">
                <?= Pagination::create($count, $page, $limit)->asLinks(function ($page) use ($controller) {
                    return $controller->index($page);
                }) ?>
            </td>
        </tr>
    </tfoot>
<? endif; ?>
</table>

<? endif; ?>
