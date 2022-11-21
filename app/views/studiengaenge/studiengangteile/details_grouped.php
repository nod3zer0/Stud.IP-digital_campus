<?php
/**
 * @var Studiengaenge_StudiengangteileController $controller
 * @var StudiengangTeil[] $stgteile
 * @var Icon $ampel_icon
 * @var string $ampelstatus
 */
?>
<td colspan="3">
    <table class="default">
        <tbody>
            <? foreach ($stgteile as $stgteil) : ?>
                <tr>
                    <td>
                        <? if (!empty($ampel_icon)) : ?>
                            <?= $ampel_icon->asImg(['title' => $ampelstatus, 'style' => 'vertical-align: text-top;']) ?>
                        <? endif; ?>
                        <?= htmlReady($stgteil->getDisplayName()) ?>
                    </td>
                    <td class="actions" style="white-space: nowrap; width: 1%;">
                        <? $actionMenu = ActionMenu::get()->setContext($stgteil->getDisplayName()) ?>
                        <? if (MvvPerm::havePermWrite($stgteil)) : ?>
                            <? $actionMenu->addLink(
                                $controller->action_url('stgteil/' . $stgteil->id),
                                _('Studiengangteil bearbeiten'),
                                Icon::create('edit', Icon::ROLE_CLICKABLE , ['title' => _('Studiengangteil bearbeiten')]))
                            ?>
                        <? endif; ?>
                        <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                            <? $actionMenu->addLink(
                                $controller->action_url('copy/' . $stgteil->id),
                                _('Studiengangteil kopieren'),
                                Icon::create('files', Icon::ROLE_CLICKABLE , ['title' => _('Studiengangteil kopieren')]))
                            ?>
                        <? endif; ?>
                        <? if (MvvPerm::havePermCreate($stgteil)) : ?>
                            <? $actionMenu->addButton(
                                'delete_part',
                                _('Studiengangteil löschen'),
                                Icon::create(
                                    'trash',
                                    Icon::ROLE_CLICKABLE ,
                                    ['title'        => _('Studiengangteil löschen'),
                                     'formaction'   => $controller->action_url('delete/' . $stgteil->getId()),
                                     'data-confirm' => sprintf(_('Wollen Sie wirklich den Studiengangteil "%s" löschen?'), $stgteil->getDisplayName())]
                                ))
                            ?>
                        <? endif; ?>
                        <?= $actionMenu->render() ?>
                    </td>
                </tr>
            <? endforeach; ?>
        </tbody>
    </table>
</td>
