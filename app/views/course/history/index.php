<?php
$sanitizeEvent = function (LogEvent $event) {
    $info = preg_replace('/chdate:\s+(\d+)\s+=&gt;\s+(\d+)/', '', $event->formatEvent());
    $info = str_replace('admission_turnout', _('max. Teilnehmerzahl'), $info);
    $info = str_replace('=&gt;', '&rarr;', $info);

    return $info;

};
?>
<? if (!$history): ?>
    <?= MessageBox::info(_('Es konnten keine Logeinträge gefunden werden.'))->hideClose() ?>
<? else: ?>
    <table class="default collapsable">
        <colgroup>
            <col style="width: 150px">
            <col>
        </colgroup>
    <? foreach ($history as $index => $type): ?>
        <tbody <? if ($index > 0) echo 'class="collapsed"'; ?>>
            <tr class="header-row">
                <th colspan="2" class="toggle-indicator">
                    <a class="toggler">
                        <?= htmlReady($type['name']) ?>
                    </a>
                </th>
            </tr>
        <? foreach ($type['events'] as $event): ?>
            <tr>
                <td><?= strftime('%x %X', $event->mkdate) ?></td>
                <td>
                    <?= $sanitizeEvent($event) ?>
                <? if ($event->info && $GLOBALS['perm']->have_perm('root')): ?>
                    <br><?= _('Info') ?>: <?= htmlReady($event->info) ?>
                <? endif ?>
                <? if ($event->dbg_info && $GLOBALS['perm']->have_perm('root')): ?>
                    <br><?= _('Debug') ?>: <?= htmlReady($event->dbg_info) ?>
                <? endif ?>
                </td>
            </tr>
        <? endforeach ?>
        </tbody>
    <? endforeach ?>
    </table>
<? endif; ?>
