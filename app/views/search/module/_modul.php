<?php
/**
 * @var string|null $modul_id
 * @var Modul $modul
 * @var Semester $selected_semester
 * @var Search_ModuleController $controller
 */
?>
<tbody class="<?= (isset($modul_id) && $modul_id == $modul->id) ? 'not-collapsed' : 'collapsed' ?>">
    <tr class="table-header header-row" id="modul_<?= htmlReady($modul->id) ?>">
        <td style="vertical-align: middle; text-align: center;">
            <a data-dialog="size=auto" title="<?= htmlReady($modul->getDisplayName()) . ' (' . _('Vollständige Modulbeschreibung') . ')' ?>" href="<?= $controller->link_for('shared/modul/description/' . $modul->id) ?>">
                <?= Icon::create('log')->asImg(['title' => _('Vollständige Modulbeschreibung')]) ?>
            </a>
        </td>
    <? if (count($modul->getAssignedCoursesBySemester($selected_semester->id, $GLOBALS['user']->id))) : ?>
        <td class="toggle-indicator">
            <a class="mvv-search-modules-row-link mvv-load-in-new-row" href="<?= $controller->action_link("details/{$modul->id}/#{$modul->id}") ?>">
                <?= htmlReady($modul->getDisplayName()) ?>
            </a>
        </td>
    <? else : ?>
        <td class="mvv-search-modules-row">
            <?= htmlReady($modul->getDisplayName()) ?>
        </td>
    <? endif; ?>
        <td class="dont-hide">
            <?= htmlReady($modul->getDisplaySemesterValidity()) ?>
        </td>
        <td class="dont-hide">
        <? if ($modul->responsible_institute->institute) : ?>
            <?=  htmlReady($modul->responsible_institute->institute->getDisplayName()); ?>
        <? endif; ?>
        </td>
    </tr>
<? if (isset($details_id) && $details_id == $modul->id): ?>
    <?= $this->render_partial('search/module/details') ?>
<? endif; ?>
</tbody>
