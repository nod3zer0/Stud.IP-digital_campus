<?php
/**
 * @var Studiengaenge_VersionenController $controller
 * @var StudiengangStgteil $stgteil
 * @var StgteilVersion[] $versionen
 * @var int $page
 */
?>
<? $count = isset($versionen) ? count($versionen) : 0; ?>
<div class="mvv-version-content">
    <table class="default collapsable">
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('studiengaenge/versionen/', _('Studiengangteil-Version'), 'start_sem') ?>
                <th colspan="2" style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? if ($count) : ?>
            <?= $this->render_partial('studiengaenge/versionen/versionen') ?>
        <? endif; ?>
        <? if ($count > MVVController::$items_per_page) : ?>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">
                        <?
                        $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                        $pagination->clear_attributes();
                        $pagination->set_attribute('perPage', MVVController::$items_per_page);
                        $pagination->set_attribute('num_postings', $count);
                        $pagination->set_attribute('page', $page);
                        $pagination->set_attribute('pagelink', '?page=%s');
                        echo $pagination->render('shared/pagechooser');
                        ?>
                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
    <? if (!empty($stgteil) && !$count) : ?>
        <div>
            <?= sprintf(_('Für den Studiengangteil <strong>%s</strong> wurden noch keine Versionen angelegt. '), htmlReady($stgteil->getDisplayName())) ?>
        </div>
        <a href="<?= $controller->url_for('studiengaenge/versionen/version') ?>">
            <?= Studip\LinkButton::create(
                    _('Eine neue Version anlegen.'),
                    $controller->action_url('version/' .  $stgteil->id),
                    ['title' => _('Eine neue Version anlegen')]
            ) ?>
        </a>
    <? endif; ?>
</div>
