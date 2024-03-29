<?php
/**
 * @var Studiengaenge_FachbereichestgteileController $controller
 * @var StudiengangTeil[] $fachbereiche
 * @var array $stgteil_ids
 * @var string $details_id
 */
?>
<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default collapsable">
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('studiengaenge/fachbereichestgteile/', _('Fachbereich'), 'fachbereich') ?>
                <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? foreach ($fachbereiche as $fachbereich) : ?>
            <tbody class="<?= $fachbereich['stgteile'] ? '' : 'empty' ?> <?= ((count($stgteil_ids) || $details_id === $fachbereich['institut_id']) ? 'not-collapsed' : 'collapsed') ?>">
                <tr class="header-row">
                    <td class="toggle-indicator">
                        <? if ($fachbereich['stgteile']) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->action_link('details_fachbereich/' . $fachbereich['institut_id']) ?>">
                                <?= htmlReady($fachbereich['name']) ?>
                            </a>
                        <? else: ?>
                            <?= htmlReady($fachbereich['name']) ?>
                        <? endif; ?>
                    </td>
                    <td class="actions dont-hide" style="white-space: nowrap;">
                        <? if (MvvPerm::havePermCreate('StudiengangTeil')) : ?>
                            <a href="<?= $controller->action_link('stgteil_fachbereich/' . $fachbereich['institut_id']) ?>">
                                <?= Icon::create('file', Icon::ROLE_CLICKABLE , ['title' => _('Neuen Studiengangteil in diesem Fachbereich anlegen')])->asImg(); ?>
                            </a>
                        <? endif; ?>
                    </td>
                </tr>
            <? if (isset($details_id) && $details_id === $fachbereich['institut_id'] || count($stgteil_ids)) : ?>
                <? $stgteile = StudiengangTeil::findByFachbereich($fachbereich['institut_id'], ['mvv_stgteil.stgteil_id' => $stgteil_ids], 'fach_name,zusatz,kp', 'ASC'); ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengangteile/details_grouped', compact('stgteile')) ?>
                </tr>
            <? endif; ?>
            </tbody>
        <? endforeach ?>
    </table>
</form>
