<?= $this->controller->jsUrl() ?>
<form method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <table id="abschluss_kategorien" class="default sortable collapsable">
        <thead>
            <tr>
                <th>
                    <?= _('Name') ?>
                </th>
                <th style="text-align: center; width: 5%;">
                    <?= _('Abschlüsse') ?>
                </th>
                <th style="text-align: center; width: 5%;">
                    <?= _('Materialien') ?>
                </th>
                <th style="width: 5%; text-align: right;"><?= _('Aktionen') ?></th>
            </tr>
        </thead>
        <? if(count($abschluss_kategorien)) : ?>
            <? foreach ($abschluss_kategorien as $kategorie) : ?>
                <? $perm = MvvPerm::get($kategorie) ?>
                <? $abschluesse = $kategorie->abschluesse; ?>
                <tbody id="<?= $kategorie->id ?>"
                       class="<?= count($abschluesse) ? '' : 'empty' ?> collapsed<?= $perm->haveFieldPerm('position') ? ' sort_items' : '' ?>">
                    <tr class="header-row sort_item">
                        <td class="toggle-indicator">
                            <? if (count($abschluesse) < 1): ?>
                                <?= htmlReady($kategorie->name) ?>
                            <? else: ?>
                                <a class="mvv-load-in-new-row"
                                   href="<?= $controller->action_link('details/' . $kategorie->id) ?>"><?= htmlReady($kategorie->name) ?> </a>
                            <? endif; ?>
                        </td>
                        <td class="dont-hide" style="text-align: center;">
                            <?= $kategorie->count_abschluesse ?>
                        </td>
                        <td class="dont-hide" style="text-align: center;">
                            <?= $kategorie->count_dokumente ?>
                        </td>
                        <td style="white-space: nowrap;" class="dont-hide actions">
                            <? if ($perm->havePermWrite()) : ?>
                                <a href="<?= $controller->action_link('kategorie/' . $kategorie->id) ?>">
                                    <?= Icon::create('edit', Icon::ROLE_CLICKABLE, tooltip2(_('Abschluss-Kategorie bearbeiten')))->asImg(); ?>
                                </a>
                            <? endif; ?>
                            <? if ($perm->havePermCreate()) : ?>
                                <? if (count($abschluesse) < 1) : ?>
                                    <?= Icon::create('trash', Icon::ROLE_CLICKABLE, tooltip2(_('Abschluss-Kategorie löschen')))->asInput(
                                        [
                                            'formaction'   => $controller->action_url('delete', $kategorie->id),
                                            'data-confirm' => sprintf(_('Wollen Sie wirklich die Abschluss-Kategorie "%s" löschen?'), $kategorie->name),
                                            'name'         => 'delete'
                                        ]); ?>
                                <? else : ?>
                                    <?= Icon::create('trash', Icon::ROLE_INACTIVE, tooltip2(_('Löschen nicht möglich')))->asImg(); ?>
                                <? endif; ?>
                            <? endif; ?>
                        </td>
                    </tr>
                </tbody>
            <? endforeach; ?>
        <? else : ?>
            <tbody>
                <tr>
                    <td colspan="4" style="text-align: center">
                        <?= _('Es wurden noch keine Abschluss-Kategorien angelegt.') ?>
                    </td>
                </tr>
            </tbody>
        <? endif?>
    </table>
</form>
