<?php
/**
 * @var Studiengaenge_FachbereicheController $controller
 * @var Fachbereich[] $fachbereiche
 * @var string $fachbereich_id
 */
?>
<table class="default collapsable" style="width: 100%;">
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('/index', _('Fachbereich'), 'name') ?>
            <?= $controller->renderSortLink('/index', _('Studiengänge'), 'count_objects', ['style' => 'text-align: center; width: 10%;']) ?>
            <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? foreach ($fachbereiche as $fachbereich) : ?>
        <?php
        // skip unknown Fachbereiche
        if (is_null($fachbereich->name)) {
            continue;
        }
        ?>
        <tbody class="<?= ($fachbereich->count_objects ? '' : 'empty') ?> <?= ($fachbereich_id === $fachbereich->id ? 'not-collapsed' : 'collapsed') ?>">
            <tr class="header-row" id="fachbereich_<?= $fachbereich->id ?>">
                <td class="toggle-indicator">
                    <? if (is_null($fachbereich->name) && $fachbereich->count_objects) : ?>
                        <a class="mvv-load-in-new-row"
                           href="<?= $controller->action_link('details/' . $fachbereich->id) ?>">
                            <?= _('Keinem Fachbereich zugeordnet') ?>
                        </a>
                    <? else : ?>
                        <? if ($fachbereich->count_objects) : ?>
                            <a class="mvv-load-in-new-row"
                               href="<?= $controller->action_link('details/' . $fachbereich->id) ?>">
                                <?= htmlReady($fachbereich->getDisplayName()) ?>
                            </a>
                        <? else : ?>
                            <?= htmlReady($fachbereich->getDisplayName()) ?>
                        <? endif; ?>
                    <? endif; ?>
                </td>
                <td style="text-align: center;" class="dont-hide"><?= $fachbereich->count_objects ?></td>
                <td></td>
            </tr>
            <? if (isset($fachbereich_id) && $fachbereich_id === $fachbereich->id) : ?>
                <tr class="loaded-details nohover">
                    <?= $this->render_partial('studiengaenge/studiengaenge/details') ?>
                </tr>
            <? endif; ?>
        </tbody>
    <? endforeach; ?>
</table>
