<?php
/**
 * @var Studiengaenge_StudiengaengeController $controller
 * @var int $count
 * @var int $page
 * @var string $msg
 */
?>
<?= $controller->jsUrl() ?>
<table class="default collapsable">
    <caption>
        <?= _('Liste der Studiengänge')?>
        <span class="actions"><? printf(_('%s Studiengänge'), $count) ?></span>
    </caption>
    <colgroup>
        <col>
        <col style="width: 15%;">
        <col style="width: 15%;">
        <col style="width: 8%; white-space: nowrap;">
    </colgroup>
    <thead>
        <tr class="sortable">
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Studiengang'), 'name') ?>
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Einrichtung'), 'institut_name', ['style' => 'width: 15%;']) ?>
            <?= $controller->renderSortLink('studiengaenge/studiengaenge/index/', _('Kategorie'), 'kategorie_name', ['style' => 'width: 15%;']) ?>
            <th style="width: 8%; text-align: right;"><?= _('Aktionen') ?></th>
        </tr>
    </thead>
    <? if ($count) : ?>
        <?= $this->render_partial('studiengaenge/studiengaenge/studiengaenge') ?>
        <? if ($count > MVVController::$items_per_page) : ?>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right;">
                        
                        <?
                        $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                        $pagination->clear_attributes();
                        $pagination->set_attribute('perPage', MVVController::$items_per_page);
                        $pagination->set_attribute('num_postings', $count);
                        $pagination->set_attribute('page', $page);
                        $parts = explode('?', $controller->action_url('index'));
                        $page_link = reset($parts) . '?page_studiengaenge=%s';
                        $pagination->set_attribute('pagelink', $page_link);
                        echo $pagination->render('shared/pagechooser');
                        ?>

                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    <? else : ?>
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center">
                    <?= $msg ?>
                </td>
            </tr>
        </tbody>
    <? endif ?>
</table>
