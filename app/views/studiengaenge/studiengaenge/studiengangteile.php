<?php
/**
 * @var Studiengaenge_StudiengaengeController $controller
 * @var string $stg_stgbez_id
 * @var Studiengang $studiengang
 * @var StudiengangTeil[] $stgteile
 * @var StgteilBezeichnung $stg_bez
 * @var QuickSearch $search
 * @var string $qs_search_id
 */
?>
<td colspan="5">
    <table id="stgteilbez_<?= $stg_stgbez_id ?>" class="default sortable">
        <colgroup>
            <col>
            <col width="1%">
        </colgroup>
        <? foreach ($stgteile as $stgteil) : ?>
            <tbody id="<?= $stg_stgbez_id . '_' . $stgteil->id ?>"<?= MvvPerm::haveFieldPermStudiengangteil($studiengang, MvvPerm::PERM_WRITE) ? 'class="sort_items"' : '' ?>>
                <tr class="sort_item">
                    <td width="90%"><?= htmlReady($stgteil->getDisplayName()) ?></td>
                    <td class="actions">
                        <? if (MvvPerm::haveFieldPermStudiengangteil($studiengang, MvvPerm::PERM_CREATE)) : ?>
                            <? if ($stg_bez) : ?>
                                <? $msg = sprintf(
                                        _('Wollen Sie die Zuordnung des Studiengangteils "%s" als "%s" zum Studiengang "%s" wirklich löschen?'),
                                        htmlReady($stgteil->getDisplayName()),
                                        htmlReady($stg_bez->getDisplayName()),
                                        htmlReady($studiengang->getDisplayName())
                                ) ?>
                                <form action="<?= $controller->action_link('delete_stgteilmf/' . $studiengang->id, $stgteil->id, $stg_bez->id) ?>"
                                      method="post">
                                    <?= CSRFProtection::tokenTag(); ?>
                                    <?= Icon::create(
                                        'trash',
                                        Icon::ROLE_CLICKABLE,
                                        ['title' => _('Zuordnung des Studiengangteils löschen')]
                                    )->asInput(['data-confirm' => $msg]); ?>
                                </form>
                            <? else : ?>
                                <? $msg = sprintf(
                                        _('Wollen Sie die Zuordnung des Studiengangteils "%s" zum Studiengang "%s" wirklich löschen?'),
                                        htmlReady($stgteil->getDisplayName()),
                                        htmlReady($studiengang->getDisplayName())
                                ) ?>
                                <form action="<?= $controller->action_link('delete_stgteil/' . $studiengang->id, $stgteil->id) ?>"
                                      method="post">
                                    <?= CSRFProtection::tokenTag(); ?>
                                    <?= Icon::create(
                                        'trash',
                                        Icon::ROLE_CLICKABLE ,
                                        ['title' => _('Zuordnung des Studiengangteils löschen')]
                                    )->asInput(['data-confirm' => $msg]); ?>
                                </form>
                            <? endif; ?>
                        <? endif; ?>
                    </td>
                </tr>
            </tbody>
        <? endforeach; ?>
        <? if (MvvPerm::haveFieldPermStudiengangteil($studiengang, MvvPerm::PERM_CREATE)) : ?>
            <tbody>
                <tr>
                    <td colspan="2">
                        <form style="width: 100%;"
                              action="<?= $controller->action_link('add_stgteil/' . $studiengang->id) ?>" method="post">
                            <?= CSRFProtection::tokenTag() ?>
                            <?= _('Studiengangteil hinzufügen') ?></div>
                            <?= $search->render() ?>
                            <?= Icon::create(
                                'search',
                                Icon::ROLE_CLICKABLE ,
                                [
                                    'title'          => _('Studiengangteil suchen'),
                                    'name'           => 'search_stgteil',
                                    'data-qs_name'   => $search->getId(),
                                    'data-qs_id'     => $qs_search_id,
                                    'data-qs_submit' => '1',
                                    'class'          => 'mvv-qs-button'
                                ])->asInput(); ?>
                            <?= Icon::create(
                                'accept',
                                Icon::ROLE_CLICKABLE ,
                                [
                                    'title' => _('Studiengangteil zuordnen')
                                ])->asInput(['class' => 'mvv-submit', 'name' => 'add_stgteil']); ?>
                            <? if ($stg_bez) : ?>
                                <input type="hidden" name="stgteil_bez_id" value="<?= $stg_bez->id ?>">
                            <? endif; ?>
                            <input type="hidden" name="level" value="stgteilbez">
                        </form>
                    </td>
                </tr>
            </tbody>
        <? endif; ?>
    </table>
</td>
