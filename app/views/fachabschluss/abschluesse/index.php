<form method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <table class="default collapsable">
        <caption>
            <?= _('Abschlüsse mit verwendeten Fächern') ?>
            <span class="actions"><? printf(ngettext('%s Abschluss', '%s Abschlüsse', $count), $count) ?></span>
        </caption>
        <thead>
            <tr class="sortable">
                <?= $controller->renderSortLink('/index', _('Abschluss'), 'name') ?>
                <?= $controller->renderSortLink('/index', _('Abschluss-Kategorie'), 'kategorie_name', ['style' => 'width: 30%;']) ?>
                <?= $controller->renderSortLink('/index', ('Fächer'), 'count_faecher', ['style' => 'width: 5%;']) ?>
                <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? foreach ($abschluesse as $abschluss) : ?>
        <tbody class="<?= $abschluss->count_faecher ? '' : 'empty' ?> <?= ($abschluss_id ? 'not-collapsed' : 'collapsed') ?>">
        <tr class="header-row">
            <td class="toggle-indicator">
                <? if ($abschluss->count_faecher) : ?>
                    <a class="mvv-load-in-new-row" href="<?= $controller->action_link('details/' . $abschluss->id) ?>"><?= htmlReady($abschluss->name) ?> </a>
                <? else: ?>
                    <?= htmlReady($abschluss->name) ?>
                <? endif; ?>
            </td>
            <td class="dont-hide"><?= htmlReady($abschluss->kategorie_name) ?></td>
            <td style="text-align: center;" class="dont-hide"><?= $abschluss->count_faecher ?></td>
            <td class="dont-hide actions" style="white-space: nowrap;">
            <? if (MvvPerm::havePermWrite($abschluss)) : ?>
                <a href="<?=$controller->action_link('abschluss/' . $abschluss->id)?>">
                    <?= Icon::create('edit', Icon::ROLE_CLICKABLE, tooltip2(_('Abschluss bearbeiten')))->asImg(); ?>
                </a>
            <? endif; ?>
            <? if (MvvPerm::havePermCreate($abschluss)) : ?>
                <? if (!$abschluss->count_faecher) : ?>
                <?= Icon::create('trash', Icon::ROLE_CLICKABLE, tooltip2(_('Abschluss löschen')))->asInput(
                        [
                            'formaction'   => $controller->action_url('delete/' . $abschluss->id),
                            'data-confirm' => sprintf(
                                _('Wollen Sie wirklich den Abschluss "%s" löschen?'),
                                $abschluss->name
                            ),
                            'name'         => 'delete'
                        ]); ?>
                <? else : ?>
                    <?= Icon::create('trash', Icon::ROLE_INACTIVE, tooltip2(_('Abschluss kann nicht glöscht werden')))->asImg(); ?>
                <? endif; ?>
            <? endif; ?>
            </td>
        </tr>
        </tbody>
        <? endforeach; ?>
        <? if ($count > MVVController::$items_per_page) : ?>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;">
                    <?php
                        $pagination = $GLOBALS['template_factory']->open('shared/pagechooser');
                        $pagination->clear_attributes();
                        $pagination->set_attribute('perPage', MVVController::$items_per_page);
                        $pagination->set_attribute('num_postings', $count);
                        $pagination->set_attribute('page', $page);
                        $page_link = reset(explode('?', $controller->action_url('index'))) . '?page_abschluesse=%s';
                        $pagination->set_attribute('pagelink', $page_link);
                        echo $pagination->render('shared/pagechooser');
                    ?>

                    </td>
                </tr>
            </tfoot>
        <? endif; ?>
    </table>
</form>
