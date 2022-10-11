<?php
/**
 * @var Studiengaenge_InformationenController $controller
 * @var Degree[] $degree
 */
?>
<table class="default collapsable">
    <colgroup>
        <col width="70%">
        <col width="29%">
        <col width="1%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Abschluss') ?></th>
            <th><?= _('Studierende') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($degree as $key => $deg) : ?>
        <? if ($deg->count_user > 0) : ?>
            <tbody class="collapsed">
                <tr class="table-header header-row">
                    <td class="toggle-indicator">
                        <a id="<?= $deg->abschluss_id ?>" class="mvv-load-in-new-row"
                           href="<?= $controller->action_link('showstudycourse', $deg->abschluss_id, $key + 1) ?>">
                            <?= htmlReady($deg->name) ?>
                        </a>
                    </td>
                    <td>
                        <?= $deg->count_user ?>
                    </td>
                    <td class="dont-hide actions">
                        <? if ($GLOBALS['perm']->have_perm('root', $GLOBALS['user']->id)) : ?>
                            <a href="<?= $controller->messagehelper(['abschluss_id' => $deg->abschluss_id]) ?>"
                               data-dialog>
                                <?= Icon::create('mail')->asImg(
                                    ['title' => htmlReady(sprintf(
                                        _('Alle Studierenden mit dem Studienabschluss %s benachrichtigen.'),
                                        $deg->name))
                                    ]) ?>
                            </a>
                        <? endif ?>
                    </td>
                </tr>
            </tbody>
        <? endif ?>
    <? endforeach ?>
</table>
