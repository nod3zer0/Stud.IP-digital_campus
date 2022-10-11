<?php
/**
 * @var Studiengaenge_VersionenController $controller
 * @var StgteilAbschnitt[] $abschnitte
 * @var StgteilVersion $version
 * @var string $abschnitt_id
 * @var string $qs_search_modul_version_id
 * @var QuickSearch $search_modul_version
 */
?>
<td colspan="3">
    <table id="abschnitte_<?= $version->id ?>" class="default collapsable sortable">
        <colgroup>
            <col>
            <col style="width: 1%;">
        </colgroup>
        <? foreach ($abschnitte as $abschnitt) : ?>
            <tbody id="<?= $abschnitt->id ?>"
                   class="<?= ($abschnitt_id === $abschnitt->id ? 'not-collapsed' : 'collapsed') ?><?= MvvPerm::haveFieldPermPosition($abschnitt, MvvPerm::PERM_WRITE) ? ' sort_items' : '' ?>">
                <tr class="header-row sort_item" id="abschnittt_<?= $abschnitt->id ?>">
                    <td class="toggle-indicator">
                        <a class="mvv-load-in-new-row"
                           href="<?= $controller->action_link('details_abschnitt/' . $abschnitt->id) ?>">
                            <?= htmlReady($abschnitt->name) ?>
                        </a>
                    </td>
                    <td class="dont-hide actions" style="white-space: nowrap;">
                        <form method="post">
                            <?= CSRFProtection::tokenTag(); ?>
                            <? $actionMenu = ActionMenu::get()->setContext($abschnitt->name) ?>
                            <? if (MvvPerm::havePermWrite($version)) : ?>
                                <? $actionMenu->addLink(
                                    $controller->action_url('abschnitt/' . $abschnitt->id),
                                    _('Studiengangteil-Abschnitt bearbeiten'),
                                    Icon::create('edit', Icon::ROLE_CLICKABLE, tooltip2(_('Studiengangteil-Abschnitt bearbeiten'))),
                                    ['data-dialog' => true])
                                ?>
                            <? endif; ?>
                            <? if (MvvPerm::haveFieldPermAbschnitte($version, MvvPerm::PERM_CREATE)) : ?>
                                <? if (!$abschnitt->count_module) : ?>
                                    <? $actionMenu->addButton(
                                        'delete',
                                        _('Studiengangteil-Abschnitt löschen'),
                                        Icon::create('trash', Icon::ROLE_CLICKABLE, tooltip2(_('Studiengangteil-Abschnitt löschen'))),
                                        [
                                            'formaction'   => $controller->action_url('delete_abschnitt/' . $abschnitt->id),
                                            'data-confirm' => sprintf(_('Wollen Sie den Studiengangteil-Abschnitt "%s" wirklich löschen?'), htmlReady($abschnitt->getDisplayName()))
                                        ]
                                    ) ?>
                                <? endif; ?>
                            <? endif; ?>
                            <?= $actionMenu->render() ?>
                        </form>
                    </td>
                </tr>
                <? if ($abschnitt_id === $abschnitt->id) : ?>
                    <tr class="loaded-details nohover">
                        <?= $this->render_partial('studiengaenge/versionen/details_abschnitt', compact('abschnitt')) ?>
                    </tr>
                <? endif; ?>
            </tbody>
        <? endforeach;
        TextHelper::reset_cycle(); ?>
        <? if (count($version->abschnitte) > 0 && MvvPerm::haveFieldPermModul_zuordnungen('StgteilAbschnitt', MvvPerm::PERM_CREATE)
        ) : ?>
            <tbody>
                <tr>
                    <td colspan="3">
                        <form class="mvv-qsform" action="<?= $controller->action_link('add_modul/' . $version->id) ?>"
                              method="post">
                            <?= _('Modul hinzufügen') ?>
                            <?= CSRFProtection::tokenTag() ?>
                            <?= $search_modul_version->render(); ?>
                            <?= Icon::create('search')->asInput([
                                'title'          => _('Modul suchen'),
                                'name'           => 'search_stgteil',
                                'data-qs_name'   => $search_modul_version->getId(),
                                'data-qs_id'     => $qs_search_modul_version_id,
                                'data-qs_submit' => 'no',
                                'class'          => 'mvv-qs-button'
                            ]) ?>
                            <label>
                                <?= _('zu Abschnitt') ?>
                                <select name="abschnitt_id">
                                    <? foreach ($abschnitte as $abschnitt) : ?>
                                        <option value="<?= $abschnitt->id; ?>"><?= htmlReady($abschnitt->getDisplayName()) ?></option>
                                    <? endforeach; ?>
                                </select>
                            </label>
                            <input name="add_modul" class="text-top mvv-submit" type="image"
                                   title="<?= _('Studiengangteil-Abschnitt hinzufügen') ?>"
                                   src="<?= Icon::create('accept')->asImagePath(); ?>">
                        </form>
                    </td>
                </tr>
            </tbody>
        <? endif; ?>
    </table>
</td>
