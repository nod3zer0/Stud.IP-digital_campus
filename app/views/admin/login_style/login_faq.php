<?php
/**
 * @var Admin_LoginStyleController $controller
 * @var LoginFaq[] $faq_entries
 */
?>
<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <table class="default">
        <caption><?= _('Hinweise zum Login') ?></caption>
        <thead>
        <tr>
            <th><?= _('Titel') ?></th>
            <th class="actions"><?= _('Aktionen') ?></th>
        </tr>
        </thead>
        <tbody>
        <? if (count($faq_entries) > 0) : ?>
            <? foreach ($faq_entries as $entry) : ?>
                <tr>
                    <td><?= htmlReady($entry->title) ?></td>
                    <td class="actions">
                        <?= ActionMenu::get()
                            ->setContext($entry->title)
                            ->addLink(
                                $controller->edit_faqURL($entry),
                                _('Hinweistext bearbeiten'),
                                Icon::create('edit'),
                                ['data-dialog' => 'size=medium']
                            )->addButton(
                                'delete',
                                _('Hinweistext löschen'),
                                Icon::create('trash'),
                                [
                                    'formaction'   => $controller->delete_faqURL($entry),
                                    'data-confirm' => sprintf(
                                        _('Wollen Sie den Hinweistext "%s" wirklich löschen?'),
                                        $entry->title
                                    ),
                                    'data-dialog'  => 'size=auto',
                                ]
                            )
                        ?>
                    </td>
                </tr>
            <? endforeach ?>
        <? else : ?>
            <tr>
                <td colspan="3" style="text-align: center">
                    <?= _('Keine Hinweistexte vorhanden') ?>
                </td>
            </tr>
        <? endif ?>
        </tbody>

    </table>
</form>
