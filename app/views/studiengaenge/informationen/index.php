<?php
/**
 * @var Studiengaenge_InformationenController $controller
 * @var Fach[] $studycourses
 */
?>
<table class="default collapsable">
    <colgroup>
        <col style="width: 70%">
        <col style="width: 29%">
        <col style="width: 1%">
    </colgroup>
    <thead>
        <tr>
            <th><?= _('Fach') ?></th>
            <th><?= _('Studierende') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($studycourses as $key => $studycourse) : ?>
        <? $count = UserStudyCourse::countBySql('fach_id = ?', [$studycourse->fach_id]); ?>
        <? if ($count > 0) : ?>
            <tbody class="collapsed">
                <tr class="table-header header-row">
                    <td class="toggle-indicator">
                        <a id="<?= $studycourse->fach_id ?>" class="mvv-load-in-new-row"
                           href="<?= $controller->showdegree($studycourse->fach_id, $key + 1) ?>">
                            <?= htmlReady($studycourse->name) ?>
                        </a>
                    </td>
                    <td>
                        <?= $count ?>
                    </td>
                    <td class="dont-hide actions">
                        <a href="<?= $controller->messagehelper(['fach_id' => $studycourse->fach_id]) ?>" data-dialog>
                            <?= Icon::create('mail')->asImg(
                                ['title' => sprintf(
                                    _('Alle Studierenden des Faches %s benachrichtigen.'),
                                    htmlReady($studycourse->name))
                                ]) ?>
                        </a>
                    </td>
                </tr>
            </tbody>
        <? endif ?>
    <? endforeach ?>
</table>
